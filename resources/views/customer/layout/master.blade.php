<!-- HEader -->        
@include('customer.layout._header')    
        
<!-- BEGIN Sidebar -->
@include('customer.layout._sidebar')
<!-- END Sidebar -->

<!-- BEGIN Content -->
<div id="main-content">
    @yield('main_content')
</div>
    <!-- END Main Content -->

<!-- Footer -->        
@include('customer.layout._footer')    
                
              