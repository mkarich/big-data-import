<style>
    #big-data-module-wrapper th{
        font-weight: bold;
    }

    #big-data-module-wrapper .remote-project-title{
        margin-top: 5px;
        margin-left: 15px;
        font-weight: bold;
    }

    #big-data-module-wrapper td.message{
        max-width: 800px;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .error-title{
        color: #a94442;
    }
    .pendingFile{padding:10px 0px;}
    .pendingFile,pendingFile a,.pendingFile a:active,.pendingFile a:hover,.pendingFile a:visited{
        text-decoration:underline;
        padding-right: 30px;
        color:#007bff !important;
        cursor:pointer;
    }
    .fa-check{
        color: green;
    }
    .fa-times,.fa-exclamation-circle, .fa-ban{
        color: red;
    }
    .dataTables_wrapperm,#big-data-module-wrapper{
        max-width: 800px;
    }
    .odd, tr .odd{
        background-color: rgba(0,0,0,.05) !important;
    }
    .table td, .table th {
        padding: .75rem !important;
        vertical-align: top;
        border-top: 1px solid #dee2e6;
    }
    .big-data-import-table{
        width: 800px !important;
    }
    .big-data-import-table .odd{
        background-color: rgba(0,0,0,.05) !important;
    }
    .big-data-import-table .even{
        background-color: #ffffff !important;
    }
    .big-data-import-table thead th, .big-data-import-table table.dataTable thead th, table.dataTable thead td {
        border:none !important;
    }
    .btn-cancel{
        color: #333;
        background-color: #fff;
        border-color: #ccc;
        font-size: 13px;
    }
    .btn-cancel:hover{
        color: #333;
        background-color: #e6e6e6;
        border-color: #adadad;
    }
</style>
<script>
    var deleteData_url = <?=json_encode($module->getUrl('deleteData.php'))?>;
    var cancelImport_url = <?=json_encode($module->getUrl('cancelImport.php'))?>;
    var pid = <?=json_encode($_GET['pid'])?>;
    $(document).ready(function() {
        $('.big-data-import-table').DataTable({
            ordering: false,
            bFilter: false,
            bLengthChange: true,
            pageLength: 50
        } );
    } );
    function fileValidation(fileInput){
        var filePath = fileInput.value;
        var allowedExtensions = /(\.csv)$/i;
        if(!allowedExtensions.exec(filePath)){
            simpleDialog('Please upload a CSV file.', '<span class="error-title">Wrong File</span>', null, 500);
            fileInput.value = '';
            $('#import').css('cursor','not-allowed');
            $('#import').prop('disabled',true);
            return false;
        }else{
            $('#import').prop('disabled',false);
            $('#import').css('cursor','pointer');
        }
    }

    function saveFilesIfTheyExist(url) {
        var files = {};
        $('#importForm').find('input').each(function(index, element){
            var element = $(element);
            var name = element.attr('name');
            var type = element[0].type;

            if (type == 'file') {
                // only store one file per variable - the first file
                jQuery.each(element[0].files, function(i, file) {
                    if (typeof files[name] == "undefined") {
                        files[name] = file;
                    }
                });
            }
        });

        var lengthOfFiles = 0;
        var formData = new FormData();
        for (var name in files) {
            lengthOfFiles++;
            formData.append(name, files[name]);   // filename agnostic
        }
        if (lengthOfFiles > 0) {
            $.ajax({
                url: url,
                data: formData,
                processData: false,
                contentType: false,
                async: false,
                type: 'POST',
                success: function(returnData) {
                    var data = JSON.parse(returnData);
                    if (data.status != 'success') {
                        simpleDialog(data.status+" One or more of the files could not be saved."+JSON.stringify(data), 'Error', null, 500);
                    }else{
                        var url = window.location.href;
                        if(url.match(/(&message=)([A-Z]{1})/)){
                            url = url.replace( /(&message=)([A-Z]{1})/, "&message=S" );
                        }else{
                            url = url + "&message=S";
                        }
                        window.location = url;
                    }
                },
                error: function(e) {
                    simpleDialog("One or more of the files could not be saved."+JSON.stringify(e), 'Error', null, 500);
                }
            });
        }
        return false;
    }

    function deleteAndCancel(edoc){
        $.ajax({
            url: deleteData_url,
            data: "&edoc="+edoc+"&pid="+pid,
            type: 'POST',
            success: function(returnData) {
                var data = JSON.parse(returnData);
                if (data.status == 'success') {
                    var url = window.location.href;
                    if(url.match(/(&message=)([A-Z]{1})/)){
                        url = url.replace( /(&message=)([A-Z]{1})/, "&message=D" );
                    }else{
                        url = url + "&message=D";
                    }
                    window.location = url;
                }else if(data.status == 'import'){
                    simpleDialog("This file is already importing and can't be deleted. Please use cancel button to cancel the import process.", 'Error', null, 500);
                }else{
                    simpleDialog(data.status+" One or more of the files could not be deleted."+JSON.stringify(data), 'Error', null, 500);
                }
            },
            error: function(e) {
                simpleDialog("One or more of the files could not be saved."+JSON.stringify(e), 'Error', null, 500);
            }
        });
    }

    function cancelImport(){
        $.ajax({
            url: cancelImport_url,
            data: "&pid="+pid,
            type: 'POST',
            success: function(returnData) {
                var data = JSON.parse(returnData);
                if (data.status == 'success') {
                    var url = window.location.href;
                    if(url.match(/(&message=)([A-Z]{1})/)){
                        url = url.replace( /(&message=)([A-Z]{1})/, "&message=C" );
                    }else{
                        url = url + "&message=C";
                    }
                    window.location = url;
                }else{
                    simpleDialog(data.status+" One or more of the files could not be deleted."+JSON.stringify(data), 'Error', null, 500);
                }
            },
            error: function(e) {
                simpleDialog("One or more of the files could not be saved."+JSON.stringify(e), 'Error', null, 500);
            }
        });
    }
</script>
<div id="big-data-info-wrapper">
    <?=$module->initializeJavascriptModuleObject()?>
    <script>
        /***SHOW DETAILS***/
        ExternalModules.Vanderbilt.BigDataImportExternalModule.details = {}

        ExternalModules.Vanderbilt.BigDataImportExternalModule.showDetails = function(logId, importdnumber){
            var width = window.innerWidth - 100;
            var height = window.innerHeight - 200;
            var content = '<pre style="max-height: ' + height + 'px">' + this.details[logId] + '</pre>'

            simpleDialog(content, 'Details Import #'+importdnumber, null, width)
        }

        ExternalModules.Vanderbilt.BigDataImportExternalModule.showSyncCancellationDetails = function(){
            var div = $('#big-data-import-module-cancellation-details').clone()
            div.show()

            var pre = div.find('pre');

            // Replace tabs with spaces for easy copy pasting into the mysql command line interface
            pre.html(pre.html().replace(/\t/g, '    '))

            ExternalModules.Vanderbilt.BigDataImportExternalModule.trimPreIndentation(pre[0])

            simpleDialog(div, 'Import Cancellation', null, 1000)
        }

        ExternalModules.Vanderbilt.BigDataImportExternalModule.trimPreIndentation = function(pre){
            var content = pre.innerHTML
            var firstNonWhitespaceIndex = content.search(/\S/)
            var leadingWhitespace = content.substr(0, firstNonWhitespaceIndex)
            pre.innerHTML = content.replace(new RegExp(leadingWhitespace, 'g'), '');
        }

    </script>
</div>
<div id="big-data-module-wrapper">
    <div style="color: #800000;font-size: 16px;font-weight: bold;"><?=$module->getModuleName()?></div>
    <br>
    <div>
        <?php
        if(array_key_exists('message', $_GET) &&  $_GET['message'] === 'S'){
            echo '<div class="alert" id="Msg" style="max-width: 1000px;background-color: #dff0d8;border-color: #d0e9c6 !important;color: #3c763d;">Your file has been uploaded.<br/>If you have set an email, a message will be sent once the import is ready, if not, refresh this page.</div>';
        }else if(array_key_exists('message', $_GET) &&  $_GET['message'] === 'D'){
            echo '<div class="alert" id="Msg" style="max-width: 1000px;background-color: #dff0d8;border-color: #d0e9c6 !important;color: #3c763d;">Your file has been deleted.</div>';
        }else if(array_key_exists('message', $_GET) &&  $_GET['message'] === 'C'){
            echo '<div class="alert" id="Msg" style="max-width: 1000px;background-color: #fff3cd;border-color: #ffeeba !important;color: #856404;">The import has been canceled and the imported file deleted.</div>';
        }
        ?>
        <form method="post" onsubmit="return saveFilesIfTheyExist('<?=$module->getUrl('saveData.php')?>');" id="importForm">
            <label style="padding-right: 30px;">Select a CSV file to import:</label>
            <input type="file" id="importFile" onchange="return fileValidation(this)">
            <input type="submit" id="import" class="btn" style="color: #fff;background-color: #007bff;border-color: #007bff;cursor:not-allowed" disabled>
        </form>

    </div>
    <div>
            <div class="pendingFile accordion_pointer"><span class="fa fa-clock fa-fw"></span> <a onclick="$('#modal-data-upload-confirmation').show()" data-toggle="collapse" data-target='#accordion'>Click here to check pending files </a></div>
            <div id='accordion' class='alert alert-primary collapse' style='border:1px solid #b8daff !important;max-width: 500px'>
            <?php
            $edoc_list = $module->getProjectSetting('edoc');
            $docs = "";
            foreach ($edoc_list as $index => $edoc){
                $sql = "SELECT stored_name,doc_name,doc_size,file_extension FROM redcap_edocs_metadata WHERE doc_id=" . $edoc;
                $q = db_query($sql);

                if ($error = db_error()) {
                    die($sql . ': ' . $error);
                }
                while ($row = db_fetch_assoc($q)) {
                    $docs .= "<div style='padding:5px'><span class='fa fa-file'></span> ".$row['doc_name']." <a onclick='deleteAndCancel(".$edoc.")'><span style='color: red;background-color: white;border-radius: 100%;cursor:pointer;' class='fa fa-times-circle'></span></a></div>";
                }
            }

            if($docs != ""){
                echo $docs;
            }else{
                echo "<div><i>None</i>";
            }
            echo "";
            ?>
            </div>
    </div>
    </div>
    <br>
    <br>
    <br>
    <h5 style="max-width: 800px;">Recent Log Entries <span><a onclick="cancelImport()" class="btn btn-cancel" style="float: right;">Cancel Current Import</a></span></h5>
    <p>(refresh the page to see the latest)</p>
    <table class="table table-striped big-data-import-table" style="max-width: 1000px;">
        <thead>
        <tr>
            <th style="min-width: 160px;">Date/Time</th>
            <th>Message</th>
            <th>Records</th>
            <th style="min-width: 125px;">Details</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $results = $module->queryLogs("
				select log_id, timestamp, message, details, import, recordlist
				order by log_id desc
				limit 2000
			");

        if($results->num_rows === 0){
            ?>
            <tr>
                <td colspan="4">No logs available</td>
            </tr>
            <?php
        }
        else{
            while($row = $results->fetch_assoc()){
                $logId = $row['log_id'];
                $details = $row['details'];
                $import = $row['import'];
                ?>
                <tr>
                    <td><?=$row['timestamp']?></td>
                    <td class="message"><?=$row['message']?></td>
                    <td ><?=$row['recordlist']?></td>
                    <td>
                        <?php if(!empty($details)) { ?>
                            <button onclick="ExternalModules.Vanderbilt.BigDataImportExternalModule.showDetails(<?=$logId?>,<?=$import?>)">Show Details</button>
                            <script>
                                ExternalModules.Vanderbilt.BigDataImportExternalModule.details[<?=$logId?>] = <?=json_encode($details)?>
                            </script>
                        <?php }else if(!empty($import)) {  ?>
                            Import # <?=$import?>
                        <?php }  ?>
                    </td>
                </tr>
                <?php
            }
        }
        ?>
        </tbody>
    </table>
</div>
