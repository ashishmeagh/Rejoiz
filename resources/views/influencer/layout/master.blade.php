<!-- HEader -->        
@include('influencer.layout._header')    
        
<!-- BEGIN Sidebar -->
@include('influencer.layout._sidebar')
<!-- END Sidebar -->

<!-- BEGIN Content -->
<div id="main-content">
    @yield('main_content')
</div>
    <!-- END Main Content -->

<!-- Footer -->        
@include('influencer.layout._footer')                
              