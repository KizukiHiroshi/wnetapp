
<form id="table_upload" method="post" action="" enctype="multipart/form-data">
    @csrf
    <div class="m-2">
        <div>
            <input type="file" name="upload_file" accept=".csv"/>
        </div>
        @include('layouts/components/button', [
            'margin'    => 'm-2',
            'value'     => '送信',
            'color'     => 'info',
            'formaction'=> '/table/csvupload_action',
        ])
    </div>
</form>
