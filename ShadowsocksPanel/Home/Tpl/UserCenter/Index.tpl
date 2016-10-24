<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Shadowsocks Panel</title>
  <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
  <!-- Bootstrap 3.3.2 -->
  <css src="{__PUBLIC__}/Css/bootstrap.min.css"/>
  <!-- Font Awesome Icons -->
  <link href="//cdn.bootcss.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css">
  <!-- Ionicons -->
  <link href="//cdn.bootcss.com/ionicons/2.0.0/css/ionicons.min.css" rel="stylesheet">
  <!-- Theme style -->
  <css src="{__PUBLIC__}/Css/AdminLTE.min.css"/>
  <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
  <css src="{__PUBLIC__}/Css/skins/_all-skins.min.css"/>
</head>
<body class="skin-blue">
  <!-- Site wrapper -->
  <div class="wrapper">
    <header class="main-header">
      <a href="/" class="logo">Shadowsocks Panel</a>
      <!-- Header Navbar: style can be found in header.less -->
      <nav class="navbar navbar-static-top" role="navigation">
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </a>

        <div class="navbar-custom-menu">
          <ul class="nav navbar-nav">
            <!-- User Account: style can be found in dropdown.less -->
            <li class="dropdown user user-menu">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <img src="{//$user->gravatar}" class="user-image" alt="User Image"/>
                <span class="hidden-xs">{$username}</span>
              </a>
              <ul class="dropdown-menu">
                <!-- User image -->
                <li class="user-header">
                  <img src="{//$user->gravatar}" class="img-circle" alt="User Image"/>
                  <p>
                    {//$user->email}
                    <small>加入时间：{//$user->regDate()}</small>
                  </p>
                </li>
                <li class="user-footer">
                  <div class="pull-left">
                    <a href="/user/profile" class="btn btn-default btn-flat">个人信息</a>
                  </div>
                  <div class="pull-right">
                    <a href="/user/logout" class="btn btn-default btn-flat">退出</a>
                  </div>
                </li>
              </ul>
            </li>
          </ul>
        </div>
      </nav>
    </header>

    <!-- Left side column. contains the sidebar -->
    <aside class="main-sidebar">
      <!-- sidebar: style can be found in sidebar.less -->
      <section class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel">
          <div class="pull-left image">
            <img src="{//$user->gravatar}" class="img-circle" alt="User Image"/>
          </div>
          <div class="pull-left info">
            <p>{//$user->user_name}</p>
            <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
          </div>
        </div>

        <ul class="sidebar-menu">
          <li>
            <a class="ajax" href="{:U('/Home/UserCenter/index')}">
              <i class="fa fa-dashboard"></i> <span>用户中心</span>
            </a>
          </li>
          <li>
            <a class="ajax" href="/user/node">
              <i class="fa fa-sitemap"></i> <span>节点列表</span>
            </a>
          </li>
          <li>
            <a class="ajax" href="/user/profile">
              <i class="fa fa-user"></i> <span>我的信息</span>
            </a>
          </li>
          <li>
            <a class="ajax" href="/user/trafficlog">
              <i class="fa fa-history"></i> <span>流量记录</span>
            </a>
          </li>
          <li>
            <a class="ajax" href="/user/edit">
              <i class="fa fa-pencil"></i> <span>修改资料</span>
            </a>
          </li>
          <li>
            <a class="ajax" href="/user/invite">
              <i class="fa fa-users"></i> <span>邀请好友</span>
            </a>
          </li>
          <if var="isAdmin">
            <li>
              <a href="/admin">
                <i class="fa fa-cog"></i> <span>管理面板</span>
              </a>
            </li>
          </if>
        </ul>
      </section>
      <!-- /.sidebar -->
    </aside>


    <div class="content-wrapper" id="content">
      <section class="content-header">
        <h1>用户中心 <small>User Center</small></h1>
      </section>

      <section class="content">
        <div class="row">
          <div class="col-md-6">
            <div class="box box-primary">
              <div class="box-header">
                <i class="fa fa-bullhorn"></i>
                <h3 class="box-title">公告&FAQ</h3>
              </div>
              <div class="box-body">
                {$msg}
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="box box-primary">
              <div class="box-header">
                <i class="fa fa-exchange"></i>
                <h3 class="box-title">流量使用情况</h3>
              </div>
              <div class="box-body">
                <div class="row">
                  <div class="col-xs-12">
                    <div class="progress progress-striped">
                      <div class="progress-bar progress-bar-primary" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: {$user->trafficUsagePercent()}%">
                        <span class="sr-only">Transfer</span>
                      </div>
                    </div>
                  </div>
                </div>
                <dl class="dl-horizontal">
                  <dt>总流量</dt>
                  <dd>{$user->enableTraffic()}</dd>
                  <dt>已用流量</dt>
                  <dd>{$user->usedTraffic()}</dd>
                  <dt>剩余流量</dt>
                  <dd>{$user->unusedTraffic()}</dd>
                </dl>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="box box-primary">
              <div class="box-header">
                <i class="fa fa-pencil"></i>
                <h3 class="box-title">签到获取流量</h3>
              </div>
              <div class="box-body">
                <p>每{$config['checkinTime']}小时可以签到一次。</p>
                <p>上次签到时间：<code>{$user->lastCheckInTime()}</code></p>
                {if $user->isAbleToCheckin() }
                <p id="checkin-btn"><button id="checkin" class="btn btn-success btn-flat">签到</button></p>
                {else}
                <p><a class="btn btn-success btn-flat disabled" href="#">不能签到</a></p>
                {/if}
                <p id="checkin-msg"></p>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="box box-primary">
              <div class="box-header">
                <i class="fa fa-paper-plane"></i>
                <h3 class="box-title">连接信息</h3>
              </div>
              <div class="box-body">
                <dl class="dl-horizontal">
                  <dt>端口</dt>
                  <dd>{$user->port}</dd>
                  <dt>密码</dt>
                  <dd>{$user->passwd}</dd>
                  <dt>自定义加密方式</dt>
                  <dd>{$user->method}</dd>
                  <dt>上次使用</dt>
                  <dd>{$user->lastSsTime()}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <script>
      $(document).ready(function () {
        $("#checkin").click(function () {
          $.ajax({
            type: "POST",
            url: "/user/checkin",
            dataType: "json",
            success: function (data) {
              $("#checkin-msg").html(data.msg);
              $("#checkin-btn").hide();
            },
            error: function (jqXHR) {
              alert("发生错误：" + jqXHR.status);
            }
          })
        })
      })
    </script>


    <footer class="main-footer">
      <strong>Copyright &copy; 2016 <a href="/">MoeHero</a>.</strong> All rights reserved.
      <div class="pull-right">
        Powered by <a href="/">MoeHero</a>
      </div>
    </footer>
  </div><!-- ./wrapper -->

  <script>
    $(document).ready(function () {
      $("#content").click(function () {
        $.ajax({
          type: "POST",
          url: "/user/checkin",
          success: function (data) {
            $("#content").html(data);
            $.getScript()
          }
        });
      });
    });
  </script>
  <!-- jQuery 2.1.3 -->
  <js src="{__PUBLIC__}/Js/jquery.min.js"/>
  <!-- Bootstrap 3.3.2 JS -->
  <js src="{__PUBLIC__}/Js/bootstrap.min.js"/>
  <!-- SlimScroll -->
  <js src="{__PUBLIC__}/Js/slimScroll/jquery.slimscroll.min.js"/>
  <!-- FastClick -->
  <js src="{__PUBLIC__}/Js/fastclick/fastclick.min.js"/>
  <!-- AdminLTE App -->
  <js src="{__PUBLIC__}/Js/app.min.js"/>
  <div style="display:none;">
    {//$analyticsCode}
  </div>
</body>
</html>