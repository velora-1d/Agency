<?php

namespace App\Services\CRM;

use App\Models\FormSubmission;
use App\Models\Pipeline;
use App\Models\PipelineStage;

class FormSubmissionLeadService
{
    public function __construct(protected LeadService $leadService)
    {
    }

    public function handle(FormSubmission $submission): void
    {
        $submission->loadMissing('form');

        $form = $submission->form;

        if (! $form || ! $form->auto_create_lead || $submission->lead_id) {
            return;
        }

        $workspace = $form->workspace()->first();

        if (! $workspace) {
            return;
        }

        $payload = $this->mapSubmissionToLeadPayload($submission->data ?? [], $workspace->getKey(), $form->name);
        $lead = $this->leadService->create($workspace, $payload);

        $submission->forceFill([
            'lead_id' => $lead->getKey(),
        ])->saveQuietly();

        $form->increment('submission_count');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mapSubmissionToLeadPayload(array $data, string $workspaceId, string $formName): array
    {
        $normalized = collect($data)
            ->mapWithKeys(fn ($value, $key) => [strtolower((string) $key) => $value])
            ->all();

        $defaultPipeline = Pipeline::query()
            ->where('workspace_id', $workspaceId)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->first();

        $defaultStage = $defaultPipeline
            ? PipelineStage::query()
                ->where('pipeline_id', $defaultPipeline->getKey())
                ->orderBy('order_index')
                ->first()
            : null;

        $name = $this->firstValue($normalized, ['name', 'full_name', 'fullname', 'contact_name']);
        $email = $this->firstValue($normalized, ['email', 'email_address']);
        $phone = $this->firstValue($normalized, ['phone', 'whatsapp', 'wa', 'mobile']);
        $company = $this->firstValue($normalized, ['company', 'company_name', 'business_name', 'brand']);
        $budget = $this->firstValue($normalized, ['budget', 'estimated_value', 'value', 'deal_value']);
        $notes = $this->buildNotes($normalized);

        return [
            'pipeline_id' => $defaultPipeline?->getKey(),
            'stage_id' => $defaultStage?->getKey(),
            'name' => $name ?: ($email ?: ($phone ?: 'Lead from ' . $formName)),
            'company' => $company,
            'email' => $email,
            'phone' => $phone,
            'source' => 'website form',
            'estimated_value' => is_numeric($budget) ? (float) $budget : null,
            'priority' => 'medium',
            'assigned_to' => null,
            'notes' => $notes,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  string[]  $keys
     */
    protected function firstValue(array $data, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $data[$key] ?? null;

            if (filled($value)) {
                return is_scalar($value) ? trim((string) $value) : null;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function buildNotes(array $data): string
    {
        return collect($data)
            ->filter(fn ($value) => filled($value))
            ->map(function ($value, $key): string {
                $formattedKey = str($key)->replace('_', ' ')->title()->toString();
                $formattedValue = is_scalar($value) ? (string) $value : json_encode($value);

                return $formattedKey . ': ' . $formattedValue;
            })
            ->implode("\n");
    }
}
