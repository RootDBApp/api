<?php

use App\Models\ReportDataView;
use App\Models\Report;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->addColumn('boolean', 'has_data_views')->default(false);
        });

        /** @var ReportDataView $reportDataView */
        foreach (ReportDataView::select('report_id')->groupBy('report_id')->get()->all() as $reportDataView) {

            $report = Report::findOrFail($reportDataView->report_id);
            $report->has_data_views = true;
            $report->update();
        }
    }
};
