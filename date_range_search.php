<?php
// Call the REDCap Connect file in the main "redcap" directory.
require_once __DIR__ . '/../../../redcap_connect.php';

global $redcap_version;

$users = REDCap::getUsers();
REDCap::allowUsers ( $users );

if (isset($_POST['startDate'])) {

    $startdate = $_POST['startDate'];
    $endate = $_POST['endDate'];

	// SQL Query using Paramaterized inputs for date range to avoid SQL injection.
    $query = "SELECT distinct a.record, b.value as FirstName, c.value as LastName, a.value as DateOfService
FROM redcap.redcap_data a
left join redcap.redcap_data b on a.project_id = b.project_id and a.record = b.record and b.field_name = 'patient_first'
left join redcap.redcap_data c on a.project_id = c.project_id and a.record = c.record and c.field_name = 'patient_last'
where a.field_name in ('billing_date', 'billing_date2', 'billing_date3', 'billing_date4', 'billing_date5', 'billing_date6')
and a.project_id = ?
and cast(a.value as date) >= ? and cast(a.value as date) <= ?";

    $table = "<thead>
    <tr>
    <th>  Record ID </th>
    <th>  First Name </th>
    <th>  Last Name </th>
    <th>  Date of Service</th>
    </tr></thead><tbody>";

    $stmt = mysqli_prepare($conn, "$query");
    mysqli_stmt_bind_param($stmt, "iss", $_GET["pid"], $startdate, $endate);
    $stmt->execute();
    $stmt->bind_result($record_id, $first_name, $last_name, $dateofservice);
    while($stmt->fetch()){
        $table .= "<tr><td><a href='/redcap_v" . $redcap_version . "/DataEntry/record_home.php?pid=7217&arm=1&id=" . $record_id . "'> "  . $record_id . "</a></td><td>" . $first_name . "</td><td>" . $last_name . "</td><td>" . $dateofservice. "</td>";
    }

    $table .= "</tbody>";

    echo $table;
    exit;
}

?>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script type='text/javascript' src='https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js'></script>

    <link rel='stylesheet' type='text/css' href='https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css'>

    <div class="page-header">

    </div>

    <div class="container">

        <div id="wait" style="display:none;width:150px;height:150px;border:1px solid black;position:absolute;top:25%;left:35%;padding:2px; background: white"><img src='loading.gif' width="150" height="150" /><br><text style='text-align: center'>Loading..</text></div>

        <form>
                <label for="daterange" class="col-sm-2 col-form-label" style="text-align: right; font-size: large">Date Range:</label>
                <div class="col-md-4">
                    <div id="daterange" class="form-control" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc;">
                        <i class="fa fa-calendar"></i>&nbsp;
                        <span></span> <i class="fa fa-caret-down"></i>
                    </div>
                </div>
        </form>
        <br/>
        <br/>
    </div>

    <div class="container">
        <br/>
        <br/>
        <table id="table" class="table">
            <thead>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            </tbody>
        </table>
    </div>

    <script type="text/javascript">

            $(document).ajaxStart(function () {
                $("#wait").css("display", "block");
            });
            $(document).ajaxComplete(function () {
                $("#wait").css("display", "none");
            });

        function populateTable(table, datatable){

            $('#table').DataTable().destroy();

            var tbody = document.getElementById('table');

            console.log(table);

            tbody.innerHTML = table;
            
            $('#table').DataTable().draw();

        }

            var start = moment().subtract(29, 'days');
            var end = moment();

            function cb(start, end) {
                $('#daterange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            }

            $('#daterange').daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Calendar Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Calendar Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            }, cb);

            cb(start, end);

            var datatable = $('#table').DataTable();

            $.ajax({
                type: "POST",
                dataType: "text",
                data: {
                    startDate: $('#daterange').data('daterangepicker').startDate.format('YYYY-MM-DD'),
                    endDate: $('#daterange').data('daterangepicker').endDate.format('YYYY-MM-DD')
                },
                success: function(data) {
                    populateTable(data, datatable);
                }
                });

        $('#daterange').on('apply.daterangepicker', function(ev, picker) {
            $.ajax({
                type: "POST",
                dataType: "text",
                data: {
                    startDate: $('#daterange').data('daterangepicker').startDate.format('YYYY-MM-DD'),
                    endDate: $('#daterange').data('daterangepicker').endDate.format('YYYY-MM-DD')
                },
                success: function(data) {
                    populateTable(data, datatable);
                }
            });
        });

    </script>
