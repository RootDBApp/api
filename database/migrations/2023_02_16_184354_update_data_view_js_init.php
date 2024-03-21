<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    public function up()
    {
        foreach (App\Models\ReportDataViewJs::all() as $dataViewJs) {

            $dataViewJs->js_init = preg_replace('`(cjs\.Chart\.getChart\()(.*)(\)\;)`', '$1refCanvas.current.id$3', $dataViewJs->js_init);
            $dataViewJs->save();
        }
    }
};
