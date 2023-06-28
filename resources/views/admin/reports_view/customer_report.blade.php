<style type="text/css">
table, th, td {
  border: 1px solid black;
}
</style>
<h2>Customer Report</h2>
<table>
 
  <tr>
    <th>ID</th>
    {{-- <th>Retailer Company Name</th> --}}
    
    {{-- <th>Company Website</th> --}}
    <th>Customer Name</th>
    <th>Email</th>
    <th>Contact No.</th>
    
    <!-- <th>Billing Address</th>
    <th>Shipping Address</th> -->
    <th>Status</th>
    <th>Country</th>
  </tr>

@foreach($data as $key => $value)
  
  <tr>
    <td>{{$value['#']}}</td>
    {{-- <td>{{$value['Customer Company Name']}}</td> --}}
   
    {{-- <td>{{$value['Company Website']}}</td> --}}
    <td>{{$value['Customer Name'] or 'NA'}}</td>
    <td>{{$value['Email'] or 'NA'}}</td>
    <td>{{$value['Contact No'] or 'NA'}}</td>
    
   <!--  <td>{{$value['Billing Address'] or 'NA' }}</td>
    <td>{{$value['Shipping Address'] or 'NA'}}</td> -->
    <td>{{$value['Status'] or 'NA'}}</td>
    <td>{{$value['Country'] or 'NA'}}</td>
  </tr>
  
  
@endforeach

  
</table>
