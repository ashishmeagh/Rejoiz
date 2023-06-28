<style type="text/css">
table, th, td {
  border: 1px solid black;
}
</style>
<h2>Influencer Report</h2>
<table>
 
  <tr>
    <th>ID</th>
    <th>Influencer Name</th>
    <th>Email</th>
    <th>Contact No.</th>
    <th>Status</th>
    <th>Country</th>
  </tr>

@foreach($data as $key => $value)
  
  <tr>
    <td>{{$value['#']}}</td>
    <td>{{$value['Influencer Name'] or 'NA'}}</td>
    <td>{{$value['Email'] or 'NA'}}</td>
    <td>{{$value['Contact No'] or 'NA'}}</td>
    <td>{{$value['Status'] or 'NA'}}</td>
    <td>{{$value['Country'] or 'NA'}}</td>
  </tr>
  
  
@endforeach

  
</table>
