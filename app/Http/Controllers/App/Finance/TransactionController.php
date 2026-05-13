<?php

namespace App\Http\Controllers\App\Finance;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\UpsertTransactionRequest;
use App\Models\Transaction;
use App\Models\Workspace;
use App\Modules\Finance\Transactions\Queries\TransactionIndexQuery;
use App\Services\Finance\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Inertia\Response;

class TransactionController extends Controller
{
    use BuildsAppShellResponse;

    public function index(Request $request, Workspace $workspace, TransactionIndexQuery $query): Response
    {
        return $this->appShell(
            workspace: $workspace,
            screen: 'Finance/Transactions/Index',
            title: 'Transactions',
            payload: $query->getIndexPayload($workspace, $request->all()),
        );
    }

    public function export(Request $request, Workspace $workspace, TransactionIndexQuery $query): StreamedResponse
    {
        $rows = $query->exportRows($workspace, $request->all());
        $filename = sprintf('transactions-%s-%s.csv', $workspace->slug, now()->format('Ymd-His'));

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'wb');

            fputcsv($handle, [
                'Date',
                'Type',
                'Category',
                'Amount',
                'Client',
                'Project',
                'Invoice',
                'Account',
                'Entry Mode',
                'Attachment',
                'Description',
            ]);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['date'],
                    $row['type'],
                    $row['category'],
                    $row['amount'],
                    $row['client'],
                    $row['project'],
                    $row['invoice'],
                    $row['account'],
                    $row['entry_mode'],
                    $row['attachment'],
                    $row['description'],
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function store(
        UpsertTransactionRequest $request,
        Workspace $workspace,
        TransactionService $service
    ): RedirectResponse {
        $service->create($workspace, $request->validated());

        return back()->with('success', 'Transaction created successfully.');
    }

    public function update(
        UpsertTransactionRequest $request,
        Workspace $workspace,
        Transaction $transaction,
        TransactionService $service
    ): RedirectResponse {
        $service->update($workspace, $transaction, $request->validated());

        return back()->with('success', 'Transaction updated successfully.');
    }

    public function destroy(
        Workspace $workspace,
        Transaction $transaction,
        TransactionService $service
    ): RedirectResponse {
        $service->delete($workspace, $transaction);

        return back()->with('success', 'Transaction deleted successfully.');
    }
}
