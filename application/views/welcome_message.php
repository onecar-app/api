<!DOCTYPE html>
<html>

<head>
    <title>Burger</title>
    <meta http-equiv="Cache-Control" content="no-cache">
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-T8Gy5hrqNKT+hzMclPo118YTQO6cYprQmhrYwIiQ/3axmI1hQomh7Ud2hPOy8SP1" crossorigin="anonymous">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
    <script src="js/script.js"></script>
    <link href="owl-carousel/owl.carousel.css" rel="stylesheet">
    <link href="owl-carousel/owl.theme.css" rel="stylesheet">
    <script type="text/javascript" src="owl-carousel/owl.carousel.js"></script>
    <script src="http://mynameismatthieu.com/WOW/dist/wow.min.js"></script>
    <link rel="stylesheet" type="text/css" href="http://master-css.com/demo/WOWjs/animate.css">
    <script type="text/javascript">
        $(document).ready(function() {

            var current_form = '#reg';

            $("#owl-demo").owlCarousel({
                slideSpeed: 300,
                paginationSpeed: 400,
                items: 1
            });

            $(".carousel").owlCarousel({
                slideSpeed: 300,
                paginationSpeed: 400,
                items: 1
            });

            $(".log-button").click(function() {
                $(".reg-button").css('color', "#a71331");
                $(".log-button").css('color', "#fff");
                $('.reg-form').hide();
                $('.log-form').show();
                $(".error-msg").hide();
                current_form = '#log';
            });

            $(".reg-button").click(function() {
                $(".log-button").css('color', "#a71331");
                $(".reg-button").css('color', "#fff");
                $('.log-form').hide();
                $('.reg-form').show();
                $(".error-msg").hide();
                current_form = '#reg';
            });

            $(".order-button").click(function(){
            	$('.order-panel').toggle();
            	if($(".order-panel").is(":visible")){
            		$(this).addClass('active');
            	} else {
            		$(this).removeClass('active');
            	}
            });

            $(".clockbtn").mouseover(function(){
                $(".clockbtn").fadeOut(200);
                $('.time-work').delay(200).fadeIn(200);
            });


            $(document).on('click', ".submit-form",function(){
                if(current_form == "#reg"){
                    if(current_form == "#reg" && $("#reg .pass").val() == $("#reg .retype").val()){
                        if($("#reg .name").val() == '' || $("#reg .email").val() == '' || $("#reg .pass").val() == '' || $("#reg .retype").val() == '' || $("#reg .city").val() == '' || $("#reg .house").val() == '' || $("#reg .street").val() == ''){

                            $(".error-msg").html("Неправильно заполнены поля");
                            $(".error-msg").show();
                        } else {                            
                            $.ajax({
                                method: "GET",
                                url: "http://burgernew.cherish.com.ua/welcome/checkemail/"+$("#reg .email").val(),
                                success:function(msg){
                                    if(msg != '1'){
                                        $(".error-msg").html("Данный email уже используется");
                                        $(".error-msg").show();
                                    } else {
                                        $(current_form).submit();
                                    }                                
                                }
                            });
                        }
                    } else {
                        $(".error-msg").html("Пароли не совпадают");
                        $(".error-msg").show();
                    }
                } else {
                    if($("#log .email").val() != '' && $("#log .pass").val() != ''){
                            $.ajax({
                                method: "GET",
                                url: "http://burgernew.cherish.com.ua/welcome/checkemailpass/"+$("#log .email").val()+"/"+$("#log .pass").val(),
                                success:function(msg){
                                    if(msg != '1'){
                                        $(".error-msg").html("Неверный email или пароль");
                                        $(".error-msg").show();
                                    } else {
                                        $(current_form).submit();
                                    }                                
                                }
                            });

                    } else {
                        $(".error-msg").html("Неправильно заполнены поля");
                        $(".error-msg").show();
                    }
                }


            });

        });
    </script>

                <script type="text/javascript">
                    $(document).ready(function(){

                        function updatecart(){
                            $.ajax({
                                method: "GET",
                                url: "http://burgernew.cherish.com.ua/welcome/updatecart",
                                success:function(msg1){
                                    $(".item-total").html(msg1);
                                }
                            });

                            $.ajax({
                                method: "GET",
                                url: "http://burgernew.cherish.com.ua/welcome/updatecartitems",
                                success:function(msg){
                                    $(".full-cart").html(msg);
                                }
                            });

                            $.ajax({
                                method: "GET",
                                url: "http://burgernew.cherish.com.ua/welcome/countcart",
                                success:function(msg){
                                    if(msg > 0){
                                        $(".main").css('margin-top', 100+msg*40+"px");
                                    }else{
                                        $(".main").css('margin-top', "100px");
                                    }
                                }
                            });
                        }

                        updatecart();

                        $(".catalog_item_buy").click(function(){
                            var id = $(this).attr('value');
                            $.ajax({
                              method: "GET",
                              url: "http://burgernew.cherish.com.ua/welcome/add_cart/"+id,
                              success:function(msg){
                                if(msg == 1)
                                    alert("Товар успешно добавлен в корзину");
                                    updatecart();
                              }
                            });
                        });

                        $(".btn-add-cart").click(function(){
                            var id = $(this).attr('value');
                            $.ajax({
                              method: "GET",
                              url: "http://burgernew.cherish.com.ua/welcome/addcart/"+id+"/"+1,
                              success:function(msg){
                                if(msg == 1){
                                    updatecart();
                                    /*
                                    $('.order-panel').toggle();
                                    if($(".order-panel").is(":visible")){
                                        $(this).addClass('active');
                                    } else {
                                        $(this).removeClass('active');
                                    }
                                    */
                                }
                              }
                            });
                            updatecart();
                        });
                    });
                </script>
    <script>
        $(document).ready(function() {
            new WOW().init();
        });
    </script>
</head>

<body>
    <header id="header">
        <div class="menu">
        	<div class="wrapper" style="background-color:#000;overflow:hidden;">
                <div class="left-menu">
                    <span>8-800-555-35-35</span>
                    <i class="fa fa-clock-o clockbtn"></i>
                    <span class="time-work" style="margin-left:20px; display:none;">8:00 - 22:00</span>
                </div>
                <div class="right-menu">
                    <ul>
                        <li><a href="#catalog" style="color:#b71637 !important;" href="#">Меню</a>
                        </li>
                        <li><a href="#meat-info">О мясе</a>
                        </li>
                        <li><a href="#delivery">Доставка</a>
                        </li>
                        <li><a href="#" class="order-button">Заказ</a>
                        </li>
                        <li><a href="#contacts">Контакты</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="full-cart"></div>
	        <div class="order-panel" style="display:none;">
	            <div class="block-order">
	                <div class="wrapper">
	                    <span><a href="#" class="log-button" style="color:#a71331;">Войти</a> / <a href="#" class="reg-button">Регистрация</a></span>
	                    <form class="reg-form" id="reg" method="post" action="http://burgernew.cherish.com.ua/welcome/buy">
	                        <div class="form-item">
	                            <label>имя*</label>
	                            <input type="text" name="name" required class="name">
	                        </div>
	                        <div class="form-item">
	                            <label>email*</label>
	                            <input type="text" name="email" required class="email">
	                        </div>
	                        <div class="form-item">
	                            <label>телефон*</label>
	                            <input type="text" name="phone" required class="phone">
	                        </div>
	                        <div class="form-item">
	                            <label>пароль*</label>
	                            <input class="med pass " type="password" name="pass" required>
	                        </div>
	                        <div class="form-item last">
	                            <label>еще раз*</label>
	                            <input class="med retype" type="password" name="retype" required>
	                        </div>

	                        <span style="color:#fff;">АДРЕС</span>

	                        <div class="form-item first">
	                            <label>город*</label>
	                            <input type="text" name="city" required class="city" style="margin:0;">
	                        </div>
	                        <div class="form-item">
	                            <label>улица*</label>
	                            <input type="text" name="street" required class="street">
	                        </div>
	                        <div class="form-item">
	                            <label>дом*</label>
	                            <input type="text" name="house" required class="house">
	                        </div>
	                        <div class="form-item">
	                            <label>подъезд</label>
	                            <input type="text" name="entrance">
	                        </div>
	                        <div class="form-item">
	                            <label>этаж</label>
	                            <input type="password" name="floor">
	                        </div>
	                        <div class="form-item">
	                            <label>квартира</label>
	                            <input type="password" name="flat">
	                        </div>
	                        <div class="form-item">
	                            <label>домофон</label>
	                            <input type="password" name="intercom">
	                        </div>
	                        <div class="form-item last">
	                            <label>комментарии</label>
	                            <input type="password" name="comment">
	                        </div>
	                    </form>
	                    <form class="log-form" id="log" method="post" action="http://burgernew.cherish.com.ua/welcome/buy" style="display:none;">
	                        <div class="form-item">
	                            <label>email</label>
	                            <input type="text" name="email" class="email" required>
	                        </div>
	                        <div class="form-item last">
	                            <label>пароль</label>
	                            <input type="password" name="pass" class="pass" required>
	                        </div>

                            <span style="color:#fff;">Дополнительно</span>

                            <div class="form-item first">
                                <label>Комментарий</label>
                                <input type="text" name="comment">
                            </div>
	                        <!-- <span style="color:#fff;">ОПЛАТА</span> -->
	                    </form>
                        <h4 class="error-msg" style="display:none;margin-top:15px;">Неправильно заполнены поля</h4>
	                </div>
	            </div>
	            <div class="block-sum">
	                <div class="wrapper">
	                    <div class="item-total first">
	                        <p class="name">ИТОГО</p>
	                        <p class="count">18</p>
	                        <p class="sum">2150</p>
	                        <a href="#" class="submit-form">готово</a>
	                    </div>
	                </div>
	            </div>
	        </div>
        </div>
    </header>
    <div class="main">
        <div class="wrapper">

            <div class="top-info">
                <div class="logo-block">
                    <img src="img/logo.png" class="logo-animation animated fadeIn">
                    <ul class="social-list animated flash">
                        <li>
                            <a href="#"><img src="img/vk.png">
                            </a>
                        </li>
                        <li>
                            <a href="#"><img src="img/inst.png">
                            </a>
                        </li>
                        <li>
                            <a href="#"><img src="img/fb.png">
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="info-block">

                    <div class="info-item">
                        <img src="img/fire.png" class="i1 animated fadeIn">
                        <p class="p1 animated fadeInLeft">ДОСТАВКА ЛЮБОГО БУРГЕРА БЕСПЛАТНО</p>
                    </div>
                    <div class="info-item">
                        <img src="img/fries.png" class="i2 animated fadeIn">
                        <p class="p2 animated fadeInLeft">ЖАРЕННАЯ КАРТОШКА К БУРГЕРУ БЕСПЛАТНО</p>
                    </div>
                    <div class="info-item last">
                        <img src="img/cow.png" class="i3 animated fadeIn">
                        <p class="p3 animated fadeInLeft">МРАМОРНАЯ ГОВЯДИНА В КАЖДОМ БУРГЕРЕ!</p>
                    </div>
                </div>
            </div>


            <img src="img/top.png" class="top" style="margin-bottom:-169px; z-index:8; position:relative;">
            <div id="owl-demo" class="owl-carousel owl-theme">

                <?foreach($sliders as $key => $slider){?>
                    <div class="item">
                        <img class="slider-img" src="img/<?=$slider['slider_image']?>">
                        <img class="danger-img" src="img/danger.png">
                        <div class="slider-header">
                            <h3><?=$slider['product']['product_name']?></h3>
                            <p><?=$slider['product']['product_info']?></p>
                        </div>
                        <div class="slider-info">
                            <p class="price"><?=$slider['product']['product_price']?> ₴</p>
                            <a class="buy btn-add-cart" href="#" value="<?=$slider['product']['product_id']?>">Заказать</a>
                        </div>
                    </div>
                <?}?>
            </div>
            <img src="img/bottom.png" class="top" style="top:-175px; z-index:8; position:relative;">

            <script type="text/javascript">
                $(document).ready(function() {
                    $(".open-info").mouseover(function() {
                        var id = $(this).attr('data-id');
                        $(".data-show-" + id).removeClass("fadeOutRight");
                        $(".data-show-" + id).addClass("fadeInRight");
                        $(".data-show-" + id).addClass("animated");
                        $(this).children("span").html("-");
                    });

                    $(".open-info").mouseout(function() {
                        var id = $(this).attr('data-id');
                        $(".data-show-" + id).removeClass("fadeInRight");
                        $(".data-show-" + id).addClass("fadeOutRight");
                        $(".data-show-" + id).addClass("animated");
                        $(this).children("span").html("+");
                    });
                });
            </script>

            <div class="catalog" id="catalog">
                <div class="row-catalog">
                <? foreach($products as $key => $product){?>
                    <? $zindex = $key*4;?>
                    <div style="position:relative;z-index:<?=$zindex?>" class="product-item <?$check =  $key+1; if($check%3 == 0 && $key != 0) echo('last');?>" style="margin-bottom:50px;">
                        <h3><?=$product['product_name']?></h3>
                        <h5><?=$product['product_info']?></h5>
                        <div class="open-info" data-id="<?=$product['product_id']?>"><span>+</span>
                        </div>
                        <div class="carousel owl-carousel owl-theme">
                            <?foreach (explode(",", $product['product_image']) as $key => $img) {?>
                                <img class="item" src="img/<?=$img?>">
                            <?}?>
                        </div>
                        <div class="burger-info data-show-<?=$product['product_id']?>" style="display:block; opacity:0;">
                            <p>
                                Состав:<br><?=$product['product_description']?>
                            </p>
                        </div>
                        <div class="product-info">
                            <p><?=$product['product_price']?> ₴</p>
                            <button class="buy btn-add-cart" value=<?=$product['product_id']?> href="#header">Заказать</button>
                        </div>
                    </div>
                <?}?>
            </div>

            <div class="meat-info wow fadeInRight" data-wow-offset="200" id="meat-info">
                <h3>АНГУС АБЕРДИН</h3>
                <img src="img/bull.png">
                <span><i>160 г </i>ЧИСТОЙ МРАМОРНОЙ ГОВЯДИНЫ</span>
                <p>Порода мясного направления родом из Шотландии. Из мяса получается нежнейший фарш, который мы используем в наших котлетах, а это 160 г чистой мраморной говядины!</p>
            </div>
        </div>
    </div>

        <div class="delivery wow fadeIn" data-wow-duration="10s" id="delivery">
            <div class="content-del">
                <div class="city">
                    <p style="padding-left:60px;">Г. Киев</p>
                    <p style="padding-left:120px;">г. Буча</p>
                    <p style="padding-left:180px;">г. Борисполь</p>
                    <p style="padding-left:245px;">г. Харьков</p>
                    <p style="padding-left:300px;color:#b71637;">ОТ 1 БУРГЕРА</p>
                    <p style="padding-left:370px;color:#b71637;">ДОСТАВКА</p>
                    <p style="padding-left:440px;color:#b71637;">БЕСПЛАТНО!</p>
                </div>
                <div class="social-list">
                    <ul>
                        <li>
                            <a href="#"><img src="img/vk_b.png">
                            </a>
                        </li>
                        <li>
                            <a href="#"><img src="img/insta_b.png">
                            </a>
                        </li>
                        <li>
                            <a href="#"><img src="img/fb_b.png">
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="contacts" id="contacts">
                    <h3>ООО “Джанк Фуд”</h3>
                    <h4>zharimmyaso@ukr.net</h4>
                    <h5>8-800-555-35-35</h5>
                </div>
            </div>
        </div>


</body>

</html>