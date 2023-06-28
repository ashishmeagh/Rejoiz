@extends('front.layout.master')
@section('main_content')
<style type="text/css">
.about-us{color:#333;}
.about-us .head-img{height: 450px;}
.about-us .head-img img{height: 100%;object-fit: cover;}
.about-us .head-part{background: linear-gradient(105.85deg, #FFB8D0 -6.07%, #FDC9AD 30.85%, #C5FFAB 64.36%, #D0FFFB 102.99%);height: 400px;padding: 50px;}
.about-us img{border-radius: 20px;width: 100%;float: left;}
.content-part{margin:0 auto;width:850px;max-width: 100%;padding: 0 15px;}
.about-us .content-part img{width:100%;margin-bottom: 20px;}
.about-us .content-part .white-box{background: #FFFFFF;box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.15);padding: 25px 20px;border-radius: 20px;margin-bottom: 20px;}
.about-us h2{margin: 0px 0px 15px 0px;font-size: 20px;font-weight: 600 !important;margin-top: 35px;display: inline-block;}
.about-us h1{margin: 0px 0px 15px 0px;font-size: 35px;font-weight: 600 !important;}

@media only screen and (max-width: 480px){
    .content-part {
        margin: 0px auto 0;
    }
    .about-us .head-part{
        height: auto;padding: 15px;
    }
    .about-us .head-img {
        height: auto;
    }
}
</style>
    
    {{Session::forget('guest_back_url')}}
    {{--  <div class="aboutus-main-header">
        <div class="overlay"></div>
        <div class="container">
             <div class="title-jst-hvt">
                <div class="col-md-12">
                    <div class="title-abts">About</div>
                    <div class="bread">
                         <a href="{{url('/')}}">Home </a>
                     <span class="slash">/</span>
                     <span class="slash last-beadcrum">About</span>   
                    </div>
                </div>
        
             
             
         </div>
        </div>
     </div>

     <div class="meetthe-principals">
         <div class="container">
             <div class="row sec1">
                 <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                     <div class="title-principals">Meet the Principals</div>
                     <div class="description-principals">
                         Do you often find yourself saying “I just gotta have it!”? Well so do we! So there you have it, by changing the “gotta” to “Got2” we found ourselves in business.<br><br>
Friends and business partners Michelle Ruby and Barbara Knight are not only crazy about shopping; they are passionate about the gift industry. They absolutely love what they do and want to share it! That is why their mission is:<br><br>
“To continually bring you the latest in fashion and trends while keeping you in touch with tradition. Searching the world for items that you simply… Just Got 2 Have!”<br><br>
We pledge to provide you the quality of service that will bring you back again and again. Our hope is to exceed your expectations every time and if we fall short, that you pick up the phone and call either one of us. We’ll not only take care of the problem, but make sure it doesn’t happen to you, or anybody else again.<br><br>
Come visit us in our Atlanta Gift Mart showroom, or give a ring to the friendly Just Got 2 Have It! representative in your area soon!
                     </div>
                 </div>
                 <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                     <div class="meet-principals-img-right">
                        <div class="abt-meet"></div> --}}
                        {{--  <img src="images/meet-the-principal-img.jpg" alt="" />
 --}}
                         {{-- <img src = "{{url('/')}}/assets/front/images/meet-the-principal-img.jpg" alt=""/>
                     </div>
                 </div>
             </div>
         </div>
     </div>

     <div class="our-team-section-abt">
        <div class="container">
            <div class="main-div-team-membr">
                 <div class="row">
                     <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                         <div class="img-ourteam">
                            <div class="border-img"></div> --}}
                             {{-- <img src="images/team-member-1.png" alt="" /> --}}
                             {{-- <img src="{{url('/')}}/assets/front/images/team-member-1.png"/>
                         </div>
                     </div>
                     <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                         <div class="our-team-descp-main">
                             <div class="user-tm-text">Christina Graff</div>
                             <div class="position-jst">Director of Finance & Administration</div>
                             <div class="direct-txt">When she first started studying Chemistry and Environmental Science, Christina Graff never imagined that she’d be working in an environment that’s quite different from her original path. As our Commissions and Account Guru, Christina not only brings an analytical head to the Just Got 2 Have It team – she also brings a warm, fun-loving spirit that creates the perfect balance for her role here.</div>
                             <div class="persnl-delts"><span><i class="fa fa-phone"></i></span> (404) 749-4850</div>
                             <div class="persnl-delts"><span><i class="fa fa-phone"></i></span> (404) 749-4851</div>
                             <div class="persnl-delts"><span><i class="fa fa-envelope"></i></span> christina@justgot2haveit.com</div>
                         </div>
                     </div>
                 </div> 
            </div>
            <div class="main-div-team-membr right-membr">
                 <div class="row sec1">
                     <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                         <div class="our-team-descp-main"> 
                             <div class="user-tm-text">Daniel Slack</div>
                             <div class="position-jst">Creative Director</div>
                             <div class="direct-txt">Daniel has years of expertise ranging from retail store owner to costume designer. He has most recently owned and operated a national visual merchandising company.<br><br>Daniel and his team have won numerous national awards for design and visual merchandising – not to mention being the sole reason behind Just Got 2 Have It’s multiple “Best of Floor” awards.”</div>
                             <div class="persnl-delts"><span><i class="fa fa-phone"></i></span> (404) 210-9783</div>
                             <div class="persnl-delts"><span><i class="fa fa-phone"></i></span> (404) 749-4851</div>
                             <div class="persnl-delts"><span><i class="fa fa-envelope"></i></span> daniel@justgot2haveit.com</div>
                         </div>
                     </div>
                     <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                         <div class="img-ourteam">
                            <div class="border-img"></div>
                              <img src="{{url('/')}}/assets/front/images/team-member-2.png"/>
                         </div>
                     </div>
                 </div>
            </div>



            <div class="main-div-team-membr">
                 <div class="row">
                     <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                         <div class="img-ourteam">
                            <div class="border-img"></div>
                              <img src="{{url('/')}}/assets/front/images/team-member-3.png"/>
                         </div>
                     </div>
                     <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                         <div class="our-team-descp-main">
                             <div class="user-tm-text">Gary Bollinger</div>
                             <div class="position-jst">Director of Digital Marketing</div>
                             <div class="direct-txt">With just under 10 years of experience in the gift and home trade, Gary believes he has found his calling. After graduating from the University of Pittsburgh with a degree in art, Gary continued to study marketing and public relations. Since his first day at his first trade show, he knew he’d forever be passionate about applying his creativity and marketing skills to the evolving industry and gets excited thinking about the major role technology will play in the future of wholesale buying habits.</div>
                             <div class="persnl-delts"><span><i class="fa fa-phone"></i></span> (724) 525-7000</div>
                             <div class="persnl-delts"><span><i class="fa fa-envelope"></i></span> gary@justgot2haveit.com</div>
                         </div>
                     </div>
                 </div>
            </div>
            <div class="main-div-team-membr right-membr">
                 <div class="row sec1">
                     <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                         <div class="our-team-descp-main">
                             <div class="user-tm-text">Hannah Davis</div>
                             <div class="position-jst">Division Coordinator, Mid-Atlantic Lifestyle, Southeast Gift & Glam Divisions</div>
                             <div class="direct-txt">Hannah Davis has been promoted to Showroom Coordinator for the SE Gift & Glam Divisions. We are excited for Hannah to transition into this new position, and feel this “co-coordinator” role will allow for a more streamlined method of management as it relates to our vendors and all of you. Please send all correspondence for the Gift and Glam division to Hannah moving forward.</div>
                             <div class="persnl-delts"><span><i class="fa fa-phone"></i></span> (404) 749-4850</div>
                             <div class="persnl-delts"><span><i class="fa fa-phone"></i></span> (404) 749-4851</div>
                             <div class="persnl-delts"><span><i class="fa fa-envelope"></i></span> hannah@justgot2haveit.com</div>
                         </div>
                     </div>
                     <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                         <div class="img-ourteam">
                            <div class="border-img"></div>
                            {{--  <img src="images/team-member-4.png" alt="" /> --}}
                            {{-- <img src="{{url('/')}}/assets/front/images/team-member-4.png"/>
                         </div>
                     </div>
                 </div>
            </div>
             <div class="main-div-team-membr">
                 <div class="row">
                     <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                         <div class="img-ourteam">
                            <div class="border-img"></div>
                              <img src="{{url('/')}}/assets/front/images/team-member-5.png"/>
                         </div>
                     </div>
                     <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                         <div class="our-team-descp-main">
                             <div class="user-tm-text">Lexie Desprez</div>
                             <div class="position-jst">Division Coordinator, Mid-Atlantic Trend, Northeast & West</div>
                             <div class="direct-txt">Lexie has joined our corporate team in the Atlanta showroom, assisting with customer service and inside sales. Lexie graduated from Kennesaw State University in 2017 with a degree in Marketing. Her previous work experience includes 3 years of retail sales and 5 years of wholesale gift showroom experience at Americas Mart. We are excited to be able to launch Lexie’s full time career and welcome her to our family.</div>
                             <div class="persnl-delts"><span><i class="fa fa-phone"></i></span> (404) 749-4850</div>
                             <div class="persnl-delts"><span><i class="fa fa-phone"></i></span> (404) 749-4851</div>
                             <div class="persnl-delts"><span><i class="fa fa-envelope"></i></span> lexie@justgot2haveit.com</div>
                         </div>
                     </div>
                 </div>
            </div>
            <hr>
            <div class="title-sale-managers">Sales Managers</div>

             <div class="main-div-team-membr">
                 <div class="row">
                     <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                         <div class="img-ourteam">
                            <div class="border-img"></div>
                             <img src="{{url('/')}}/assets/front/images/team-member-6.png"/>
                         </div>
                     </div>
                     <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                         <div class="our-team-descp-main">
                             <div class="user-tm-text">Nicole Certo</div>
                             <div class="position-jst">Sales Manager, Mid-Atlantic</div>
                             <div class="direct-txt">Nicole has worked in sales and sales management for over 25 years and she loves it. It all started as a young person when she studied Interior Design as her career of choice. Nicole quickly realized that she had entered into a sales field--she could create an amazing project but would ultimately have to ‘sell’ it to the home or business owner. Her career in sales continued, ultimately gaining experience in retail, B2B, corporate sales training, talent acquisition, and sales leadership...</div>
                             <div class="persnl-delts"><span><i class="fa fa-phone"></i></span> (412) 445-5897</div>
                             <div class="persnl-delts"><span><i class="fa fa-envelope"></i></span> nicole@justgot2haveit.com</div>
                         </div>
                     </div>
                 </div>
            </div>
            <div class="main-div-team-membr right-membr">
                 <div class="row sec1">
                     <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                         <div class="our-team-descp-main">
                             <div class="user-tm-text">Alison Blackmore</div>
                             <div class="position-jst">Sales Manager, Northeast</div>
                             <div class="direct-txt">Alison began working for Kris & Co. in 2013 as a Sales Coordinator for our NE Division. As a result of her commitment, attention to detail and acting as a tremendous resource to the sales team she rose to the role of Sales Manager in 2014.  Alison has proven to be an asset in building strong relationships with her sales team and vendor partners. Alison’s degree in Business, her experience as Sales Analyst at Honeywell Consumer Products Group and drive has made her a great fit for our organization.</div>
                             <div class="persnl-delts"><span><i class="fa fa-phone"></i></span> (281) 684-1771</div>
                             <div class="persnl-delts"><span><i class="fa fa-envelope"></i></span> alison@justgot2haveit.com</div>
                         </div>
                     </div>
                     <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                         <div class="img-ourteam">
                            <div class="border-img"></div>
                              <img src="{{url('/')}}/assets/front/images/team-member-7.png"/>
                         </div>
                     </div>
                 </div>
            </div>




            <div class="main-div-team-membr">
                 <div class="row">
                     <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                         <div class="img-ourteam">
                            <div class="border-img"></div>
                              <img src="{{url('/')}}/assets/front/images/team-member-8.png"/>
                         </div>
                     </div>
                     <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                         <div class="our-team-descp-main">
                             <div class="user-tm-text">Katie Gale</div>
                             <div class="position-jst">Sales Manager, Southeast Gift</div>
                             <div class="direct-txt">In September 2009, we were lucky enough to have Katie rejoin our organization as Sales Manager. She is the right hand to Principal Michelle Ruby when it comes to managing the reps and their territories.
Her main focus is making the day-to-day life of the team run smoothly. Whether it be training a new rep on the road or working with customers during the shows Katie always has a smile on her face and a can do attitude. Her enthusiasm is contagious.</div>
                             <div class="persnl-delts"><span><i class="fa fa-phone"></i></span> (205) 504-6826</div>
                             <div class="persnl-delts"><span><i class="fa fa-envelope"></i></span> katie@justgot2haveit.com</div>
                         </div>
                     </div>
                 </div>
            </div>
            <div class="main-div-team-membr right-membr">
                 <div class="row sec1">
                     <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                         <div class="our-team-descp-main">
                             <div class="user-tm-text">Marla Boyd</div>
                             <div class="position-jst">Sales Manager, Southeast Glam</div>
                             <div class="direct-txt">Marla is a retail professional, with a career that has spanned over 25 years. Prior to taking the position of Sales Manager, for the Southeast Glam division of Just Got 2 Have It! Marla worked for Stein Mart in a buying capacity for 21 years. Following a successful career with Stein Mart, Marla had the opportunity to work with a startup jewelry company as their National Sales Manager. Marla is an innovative merchant who has had a consistent track record throughout her entire career.</div>
                             <div class="persnl-delts"><span><i class="fa fa-phone"></i></span> (904) 874-5819</div>
                             <div class="persnl-delts"><span><i class="fa fa-envelope"></i></span> marla@justgot2haveit.com</div>
                         </div>
                     </div>
                     <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                         <div class="img-ourteam">
                            <div class="border-img"></div>
                              <img src="{{url('/')}}/assets/front/images/team-member-9.png"/>
                         </div>
                     </div>
                 </div>
            </div>
            <div class="main-div-team-membr">
                 <div class="row">
                     <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                         <div class="img-ourteam">
                            <div class="border-img"></div>
                             <img src="{{url('/')}}/assets/front/images/team-member-10.png"/>
                         </div>
                     </div>
                     <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                         <div class="our-team-descp-main">
                             <div class="user-tm-text">Kasey Carlile</div>
                             <div class="position-jst">Sales Manager, West</div>
                             <div class="direct-txt">I have been in the gift and lifestyle industry for over 20 years. I started as a territory rep for RUSS Berrie and Company, which is where I spent the bulk of my career. I was promoted to management in 2 short years, and worked my way up to running the entire West Coast sales force. As a manager at RUSS, we all kept key accounts. I have remained a sales person throughout my entire career. I feel this gives me the ability to always know what the trends are and what is happening in the industry.</div>
                             <div class="persnl-delts"><span><i class="fa fa-phone"></i></span> (949) 524-0598</div>
                             <div class="persnl-delts"><span><i class="fa fa-phone"></i></span> (702) 920-8565</div>
                             <div class="persnl-delts"><span><i class="fa fa-envelope"></i></span> kasey@justgot2haveit.com</div>
                         </div>
                     </div>
                 </div>
            </div>
            

         </div>
     </div> --}}
     
     <div class="about-us">
        <div class="head-part text-center">
            <h1>About Us</h1>
            <div class="head-img">
                <img src="{{url('/')}}/assets/front/images/abt-us.jpg" alt="">
            </div>
        </div>
        <div class="content-part">
            <h2>Why we are here</h2>
            <p>Rejoiz Information Technology Limited was founded in 2008 on the belief that the Ugandan (African) Wholesalers/Retailers/Importers should be able to explore the authentic manufacturers across the globe.</p>
            <p>Our mission is to smoothen the process of finding the best product manufacturers, shipping guidelines that too with hassle-free financial transaction for local Wholesalers/Retailers/Importers of Uganda and giving an exposure to serve an emerging Ugandan market to the suppliers all around the world.</p>
            <p>That’s why we built Rejoiz, an online wholesale marketplace empowering small to large business owners and independent brands to buy and sell wholesale products online.
            </p>
            <h2>Our Values</h2>
            <div class="row">
                <div class="col-md-6 col-sm-6">
                    <div class="white-box">
                    <img src="{{url('/')}}/assets/front/images/warehouse.jpg" alt="">
                    <p>We’ve already registered more than thousands of local Wholesalers/Retailers/Importers of Uganda and thousands of emerging and established brands, Manufacturers and Resellers across the globe.</p>
                    </div>
                </div>
                <div class="col-md-6 col-sm-6">   
                    <div class="white-box">
                        <img src="{{url('/')}}/assets/front/images/african-lady.jpg" alt="">
                        <p>We firmly believe, every buyer and seller registered on Rejoiz deserves a personal attention during the entire process of buying or selling. Hence, every inquiry will be handled by our executive personally.</p>
                    </div>           
                </div>
            </div>
        </div>

    </div>      

    <!--footer section start here-->
    <footer>
        <div id="footer"></div>
    </footer>
    <!--footer section end here-->
</body>

</html>

@endsection