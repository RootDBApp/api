<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    public function up()
    {

        $current_report_id = 0;
        $current_y = -4;
        foreach (App\Models\ReportDataView::all() as $dataView) {

            if ($current_report_id !== $dataView->report_id) {

                $current_report_id = $dataView->report_id;
                $current_y = -4;
            }

            $current_y += 4;
            $dataView->position = '{"x":0,"y":' . $current_y . ',"w":12,"h":4}';
            $dataView->save();
        }
    }
};

