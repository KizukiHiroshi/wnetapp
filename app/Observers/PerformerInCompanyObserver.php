<?php

namespace App\Observers;

use App\Models\Common\PerformerInCompany;

// 利用企業が登録更新されたときに処理すること
class PerformerInCompanyObserver
{
    /**
     * Handle the PerformerInCompany "created" event.
     *
     * @param  \App\Models\PerformerInCompany  $performerInCompany
     * @return void
     */
    public function created(PerformerInCompany $performerInCompany)
    {
        //
    }

    /**
     * Handle the PerformerInCompany "updated" event.
     *
     * @param  \App\Models\PerformerInCompany  $performerInCompany
     * @return void
     */
    public function updated(PerformerInCompany $performerInCompany)
    {
        //
        dd($performerInCompany);
    }

    /**
     * Handle the PerformerInCompany "deleted" event.
     *
     * @param  \App\Models\PerformerInCompany  $performerInCompany
     * @return void
     */
    public function deleted(PerformerInCompany $performerInCompany)
    {
        //
    }

    /**
     * Handle the PerformerInCompany "restored" event.
     *
     * @param  \App\Models\PerformerInCompany  $performerInCompany
     * @return void
     */
    public function restored(PerformerInCompany $performerInCompany)
    {
        //
    }

    /**
     * Handle the PerformerInCompany "force deleted" event.
     *
     * @param  \App\Models\PerformerInCompany  $performerInCompany
     * @return void
     */
    public function forceDeleted(PerformerInCompany $performerInCompany)
    {
        //
    }

    // 発注・受注機能有無に合わせて、シーケンスにレコードの準備があるか確認する
    private function synchronizeSequence() {

    }
}
