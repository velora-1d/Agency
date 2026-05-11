<?php

namespace App\Services\Finance;

use App\Models\ProjectFinanceSplit;
use App\Models\ProjectFinanceSplitItem;

class FinanceSplitService
{
    public function calculate(ProjectFinanceSplit $split): void
    {
        $project = $split->project;
        $totalProjectValue = $project->budget ?? 0;
        
        // If there are paid invoices, use that instead of budget
        $paidAmount = $project->invoices()->where('status', 'paid')->sum('total');
        if ($paidAmount > 0) {
            $totalProjectValue = $paidAmount;
        }

        $remaining = $totalProjectValue;

        // Step 1: Deduct Operational Costs (flat)
        $totalOperational = 0;
        foreach ($split->items->where('type', 'operational') as $item) {
            $amount = $item->flat_amount ?? 0;
            $item->updateQuietly(['final_amount' => $amount]);
            $totalOperational += $amount;
        }
        $remaining -= $totalOperational;

        // Step 2: Calculate Kas Kantor (%)
        $kasKantorAmount = $remaining * ($split->kas_kantor_percentage / 100);
        $remaining -= $kasKantorAmount;

        // Step 3: Distribute Team Fees (% or flat)
        $totalTeamFee = 0;
        foreach ($split->items->where('type', 'team_fee') as $item) {
            $amount = 0;
            if ($item->calculation_type === 'percentage') {
                $amount = $remaining * ($item->percentage / 100);
            } else {
                $amount = $item->flat_amount ?? 0;
            }
            $item->updateQuietly(['final_amount' => $amount]);
            $totalTeamFee += $amount;
        }

        // Update main split record
        $split->updateQuietly([
            'total_project_value' => $totalProjectValue,
            'total_operational_cost' => $totalOperational,
            'total_kas_kantor' => $kasKantorAmount,
            'total_team_fee' => $totalTeamFee,
            'kas_kantor_amount' => $kasKantorAmount,
        ]);
    }
}
