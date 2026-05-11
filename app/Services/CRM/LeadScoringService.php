<?php

namespace App\Services\CRM;

class LeadScoringService
{
    public function score(array $data): int
    {
        $score = 15;

        if (filled($data['email'] ?? null)) {
            $score += 15;
        }

        if (filled($data['phone'] ?? null)) {
            $score += 15;
        }

        if (filled($data['company'] ?? null)) {
            $score += 10;
        }

        if (filled($data['source'] ?? null) && in_array($data['source'], ['referral', 'website form', 'website', 'ads', 'instagram'], true)) {
            $score += 10;
        }

        if (filled($data['estimated_value'] ?? null)) {
            $value = (float) $data['estimated_value'];

            if ($value >= 50000000) {
                $score += 25;
            } elseif ($value >= 15000000) {
                $score += 18;
            } elseif ($value >= 5000000) {
                $score += 10;
            } else {
                $score += 5;
            }
        }

        if (($data['priority'] ?? null) === 'high') {
            $score += 10;
        } elseif (($data['priority'] ?? null) === 'medium') {
            $score += 5;
        }

        if (filled($data['notes'] ?? null)) {
            $score += 5;
        }

        return max(0, min(100, $score));
    }
}
