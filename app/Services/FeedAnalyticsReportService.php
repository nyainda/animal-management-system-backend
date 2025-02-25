<?php

namespace App\Services;

use App\Models\Animal;
use Illuminate\Support\Facades\DB;

class FeedAnalyticsReportService
{
    public function generate(Animal $animal)
    {
        // Consumption trend analysis (daily)
        $dailyConsumptionTrend = $this->getDailyConsumptionTrend($animal);

        // Weekly consumption trend
        $weeklyConsumptionTrend = $this->getWeeklyConsumptionTrend($animal);

        // Monthly consumption trend
        $monthlyConsumptionTrend = $this->getMonthlyConsumptionTrend($animal);

        // Feed type consumption breakdown
        $feedTypeBreakdown = $this->getFeedTypeBreakdown($animal);

        // Waste analysis
        $wasteAnalysis = $this->getWasteAnalysis($animal);

        // Cost analysis
        $costAnalysis = $this->getCostAnalysis($animal);

        // Feed efficiency metrics
        $feedEfficiency = $this->getFeedEfficiency($animal);

        // Summary statistics
        $summaryStatistics = $this->getSummaryStatistics($animal, $feedTypeBreakdown);

        // Visualization-friendly data
        $visualizationData = $this->getVisualizationData(
            $dailyConsumptionTrend,
            $weeklyConsumptionTrend,
            $monthlyConsumptionTrend
        );

        return [
            'daily_consumption_trend' => $dailyConsumptionTrend,
            'weekly_consumption_trend' => $weeklyConsumptionTrend,
            'monthly_consumption_trend' => $monthlyConsumptionTrend,
            'feed_type_breakdown' => $feedTypeBreakdown,
            'waste_analysis' => $wasteAnalysis,
            'cost_analysis' => $costAnalysis,
            'feed_efficiency' => $feedEfficiency,
            'summary_statistics' => $summaryStatistics,
            'visualization_data' => $visualizationData,
        ];
    }

    private function getDailyConsumptionTrend(Animal $animal)
    {
        return $animal->feedAnalytics()
            ->select(
                DB::raw('DATE(analysis_date) as date'),
                DB::raw('AVG(daily_consumption) as avg_consumption'),
                DB::raw('AVG(daily_cost) as avg_cost'),
                DB::raw('AVG(waste_percentage) as avg_waste')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getWeeklyConsumptionTrend(Animal $animal)
    {
        return $animal->feedAnalytics()
            ->select(
                DB::raw("EXTRACT(WEEK FROM analysis_date) as week"),
                DB::raw('AVG(daily_consumption) as avg_consumption'),
                DB::raw('AVG(daily_cost) as avg_cost'),
                DB::raw('AVG(waste_percentage) as avg_waste')
            )
            ->groupBy('week')
            ->orderBy('week')
            ->get();
    }

    private function getMonthlyConsumptionTrend(Animal $animal)
    {
        return $animal->feedAnalytics()
            ->select(
                DB::raw("EXTRACT(MONTH FROM analysis_date) as month"),
                DB::raw('AVG(daily_consumption) as avg_consumption'),
                DB::raw('AVG(daily_cost) as avg_cost'),
                DB::raw('AVG(waste_percentage) as avg_waste')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    private function getFeedTypeBreakdown(Animal $animal)
    {
        return $animal->feedAnalytics()
            ->select('feed_type_id',
                DB::raw('SUM(daily_consumption) as total_consumption'),
                DB::raw('AVG(daily_cost) as avg_cost'),
                DB::raw('AVG(waste_percentage) as avg_waste')
            )
            ->groupBy('feed_type_id')
            ->with('feedType')
            ->get();
    }

    private function getWasteAnalysis(Animal $animal)
    {
        return [
            'total_waste_percentage' => $animal->feedAnalytics()->avg('waste_percentage'),
            'highest_waste_feed_type' => $animal->feedAnalytics()
                ->select('feed_type_id')
                ->selectRaw('AVG(waste_percentage) as avg_waste')
                ->groupBy('feed_type_id')
                ->orderByDesc('avg_waste')
                ->with('feedType')
                ->first(),
            'waste_trend' => $animal->feedAnalytics()
                ->select(
                    DB::raw('DATE(analysis_date) as date'),
                    DB::raw('AVG(waste_percentage) as avg_waste')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
        ];
    }

    private function getCostAnalysis(Animal $animal)
    {
        return $animal->feedAnalytics()
            ->select(
                DB::raw('DATE(analysis_date) as date'),
                DB::raw('SUM(daily_cost) as total_cost')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getFeedEfficiency(Animal $animal)
    {
        if ($animal->weight_gain > 0) {
            return [
                'feed_conversion_ratio' => $animal->feedAnalytics()->sum('daily_consumption') / $animal->weight_gain,
                'cost_per_weight_gain' => $animal->feedAnalytics()->sum('daily_cost') / $animal->weight_gain,
            ];
        }

        return [
            'feed_conversion_ratio' => null,
            'cost_per_weight_gain' => null,
        ];
    }

    private function getSummaryStatistics(Animal $animal, $feedTypeBreakdown)
    {
        return [
            'total_consumption' => $animal->feedAnalytics()->sum('daily_consumption'),
            'total_cost' => $animal->feedAnalytics()->sum('daily_cost'),
            'avg_waste_percentage' => $animal->feedAnalytics()->avg('waste_percentage'),
            'most_efficient_feed_type' => $feedTypeBreakdown->sortBy('avg_waste')->first(),
            'least_efficient_feed_type' => $feedTypeBreakdown->sortByDesc('avg_waste')->first(),
        ];
    }

    private function getVisualizationData($dailyConsumptionTrend, $weeklyConsumptionTrend, $monthlyConsumptionTrend)
    {
        return [
            'daily_consumption_trend' => [
                'labels' => $dailyConsumptionTrend->pluck('date'),
                'consumption' => $dailyConsumptionTrend->pluck('avg_consumption'),
                'cost' => $dailyConsumptionTrend->pluck('avg_cost'),
                'waste' => $dailyConsumptionTrend->pluck('avg_waste'),
            ],
            'weekly_consumption_trend' => [
                'labels' => $weeklyConsumptionTrend->pluck('week'),
                'consumption' => $weeklyConsumptionTrend->pluck('avg_consumption'),
                'cost' => $weeklyConsumptionTrend->pluck('avg_cost'),
                'waste' => $weeklyConsumptionTrend->pluck('avg_waste'),
            ],
            'monthly_consumption_trend' => [
                'labels' => $monthlyConsumptionTrend->pluck('month'),
                'consumption' => $monthlyConsumptionTrend->pluck('avg_consumption'),
                'cost' => $monthlyConsumptionTrend->pluck('avg_cost'),
                'waste' => $monthlyConsumptionTrend->pluck('avg_waste'),
            ],
        ];
    }
}
