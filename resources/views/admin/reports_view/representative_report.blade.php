<style type="text/css">
table, th, td {
  border: 1px solid black;
}
</style>

<h2>Representative Report</h2>
<table>
 
  <tr>
    <th>ID</th>
    <th>Reps Name</th>
    <th>Contact No.</th>
    <th>Email</th>
    <th>Commission (%)</th>
    <th>Approval Status</th>
    <th>Status</th>
  </tr>

@foreach($data as $key => $value)
  
  <tr>
    <td>{{$value['#']}}</td>
    <td>{{$value['Reps Name']}}</td>
    <td>{{$value['Contact No']}}</td>
    <td>{{$value['Email']}}</td>
    <td>{{$value['Commission (%)']}}</td>
    <td>{{$value['Approval Status']}}</td>
    <td>{{$value['Status']}}</td>
  </tr>
  
@endforeach

  
</table>
