<?php

namespace App\Observers;

use App\Models\Common\PerformerInCompany;
use App\Services\Database\ExcuteProcessService;
use App\Services\Database\FindValueService;

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
        // 発注・受注機能有無に合わせて、シーケンスにレコードの準備があるか確認する
        $this->syncToSequence($performerInCompany);
    }

    /**
     * Handle the PerformerInCompany "updated" event.
     *
     * @param  \App\Models\PerformerInCompany  $performerInCompany
     * @return void
     */
    public function updated(PerformerInCompany $performerInCompany)
    {
        // 発注・受注機能有無に合わせて、シーケンスにレコードの準備があるか確認する
        $this->syncToSequence($performerInCompany);
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
    private function syncToSequence($performerInCompany) {
        if ($performerInCompany->can_order) {
            $mode = 'order';
            $this->excuteSequence($performerInCompany, $mode);
        }
        if ($performerInCompany->can_getorder) {
            $mode = 'getorder';
            $this->excuteSequence($performerInCompany, $mode);
        }
    }

    // Sequencesにインサート
    private function excuteSequence($performerInCompany, $mode) {
        $findvalueservice = new FindValueService;
        $key = $mode.'_'.$performerInCompany->sequence_key;
        $foreginkey = 'sequences?key='.urlencode($key);
        $sequence = $findvalueservice->findValue($foreginkey, 'sequence');
        if ($sequence == null) {
            $id = 0;
            $mode_j = $mode == 'order' ? '発注' : '受注';
            $form =[];
            $form['name'] = $mode_j.$performerInCompany->sequence_key;
            $form['key'] = $key;
            $form['sequence'] = '1';
            $form['nowstring'] = '2018';
            $excuteprocessservice = new ExcuteProcessService;
            $ret_id = $excuteprocessservice->excuteProcess('sequences' , $form, $id);   
        }
    }
}
