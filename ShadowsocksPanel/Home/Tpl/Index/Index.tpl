<!DOCTYPE html>
<html>
  <head>
    <title>Shadowsocks Panel</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

    <link href="//fonts.lug.ustc.edu.cn/icon?family=Material+Icons" rel="stylesheet">

    <css src="{__PUBLIC__}/Css/materialize.min.css"/>
    <css src="{__PUBLIC__}/Css/style.css"/>
  </head>
  <body>
    <nav class="light-blue lighten-1" role="navigation">
      <div class="nav-wrapper container"><a id="logo-container" href="/" class="brand-logo">Shadowsocks Panel</a>
        <ul class="right hide-on-med-and-down">
          <li><a href="https://shadowsocks.org/en/download/clients.html">客户端下载</a></li>
          <if var="login">
            <li><a href="{:U('/Home/UserCenter/index')}">用户中心</a></li>
            <li><a href="/user/logout">退出</a></li>
          <else>
            <li><a href="/auth/login">登录</a></li>
            <li><a href="/auth/register">注册</a></li>
          </if>
        </ul>

        <ul id="nav-mobile" class="side-nav">
          <li><a href="https://shadowsocks.org/en/download/clients.html">客户端下载</a></li>
          <if var="login">
            <li><a href="{:U('/Home/UserCenter/index')}">用户中心</a></li>
            <li><a href="/user/logout">退出</a></li>
          <else>
            <li><a href="/auth/login">登录</a></li>
            <li><a href="/auth/register">注册</a></li>
          </if>
        </ul>
        <a href="#" data-activates="nav-mobile" class="button-collapse"><i class="material-icons">menu</i></a>
      </div>
    </nav>
    <div class="section no-pad-bot" id="index-banner">
      <div class="container">
        <br><br>
        <h1 class="header center orange-text">Shadowsocks Panel</h1>
        <div class="row center">
          <h5 class="header col s12 light">轻松科学上网 保护个人隐私</h5>
        </div>
        <div class="row center">
          <if var="login">
            <a href="{:U('/Home/UserCenter/index')}" class="btn-large waves-effect waves-light orange">进入用户中心</a>
          <else>
            <a href="/auth/register" class="btn-large waves-effect waves-light orange">立即注册</a>
          </if>
        </div>
        {//if}
        <br><br>
      </div>
    </div>

    <div class="container">
      <div class="section">
        <div class="row">
          <div class="col s12 m4">
            <div class="icon-block">
              <h2 class="center light-blue-text"><i class="material-icons">flash_on</i></h2>
              <h5 class="center">Super Fast</h5>
              <p class="light">
                Bleeding edge techniques using Asynchronous I/O and Event-driven programming.
              </p>
            </div>
          </div>

          <div class="col s12 m4">
            <div class="icon-block">
              <h2 class="center light-blue-text"><i class="material-icons">group</i></h2>
              <h5 class="center">Open Source</h5>
              <p class="light">
                Totally free and open source. A worldwide community devoted to deliver bug-free code and long-term support.
              </p>
            </div>
          </div>

          <div class="col s12 m4">
            <div class="icon-block">
              <h2 class="center light-blue-text"><i class="material-icons">settings</i></h2>
              <h5 class="center">Easy to work with</h5>
              <p class="light">
                Avaliable on multiple platforms, including PC, MAC, Mobile (Android and iOS) and Routers (OpenWRT).
              </p>
            </div>
          </div>
        </div>
      </div>
      <br><br><br><br><br><br>
    </div>

    <footer class="page-footer orange">
      <div class="footer-copyright">
        <div class="container">
          Copyright &copy; 2016 <a href="/">MoeHero</a>. All rights reserved.
          <div class="right">
            Powered by <a href="/">MoeHero</a>
          </div>
        </div>
        <div style="display:none;">
          {//统计代码}
        </div>
      </div>
    </footer>
  </body>
</html>
