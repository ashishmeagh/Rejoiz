<!-- HEader -->        
@include('sales_manager.layout._header')    
        
<!-- BEGIN Sidebar -->
@include('sales_manager.layout._sidebar')
<!-- END Sidebar -->

<!-- BEGIN Content -->
<div id="main-content">
    @yield('main_content')
</div>
    <!-- END Main Content -->

<!-- Footer -->        
@include('sales_manager.layout._footer')    
                
              