<div id="debug">
  <script type="text/javascript">
    function debuginfo_hide() {
      $("#debuginfo").hide();
      $("#debugbutton").show();
    }

    function debuginfo_show() {
      $("#debuginfo").show();
      $("#debugbutton").hide();
    }

    $(function () {
      var src_posi_Y = 0, dest_posi_Y = 0, move_Y = 0, is_mouse_down = false, destHeight = 300;
      $("#expander").mousedown(
              function (e) {
                src_posi_Y = e.pageY;
                is_mouse_down = true;
              }
      );
      $(document).bind("click mouseup", function (e) {
        if (is_mouse_down) {
          is_mouse_down = false;
        }
      }).mousemove(
              function (e) {
                dest_posi_Y = e.pageY;
                move_Y = src_posi_Y - dest_posi_Y;
                src_posi_Y = dest_posi_Y;
                destHeight = $("#debuginfo").height() + move_Y;
                if (is_mouse_down) {
                  $("#debuginfo").css("height", destHeight > 300 ? destHeight : 300);
                }
              }
      );
    });
  </script>
  <style type="text/css">
    #expander:hover {
      cursor: n-resize;
    }

    #expander {
      width: 100%;
      height: 3px;
      background-color: #999;
    }

    #debugbutton {
      position: fixed;
      bottom: 0;
      right: 0;
      width: 30px;
      height: 30px;
    }

    #debuginfo {
      position: fixed;
      bottom: 0;
      right: 0;
      width: 100%;
      height: 300px;
      background-color: #EEE;
      -webkit-box-shadow: 0 0 10px #888;
      -moz-box-shadow: 0 0 10px #888;
      box-shadow: 0 0 10px #888;
      z-index: 999;
    }

    #debugbutton button,
    #debuginfo button {
      position: absolute;
      right: 0;
      width: 30px;
      height: 30px;
    }
  </style>
  <div id="debugbutton">
    <button onclick="debuginfo_show()">↑</button>
  </div>
  <div id="debuginfo" style="display:none;">
    <div id="expander"></div>
    <button onclick="debuginfo_hide()">↓</button>
    <div style="margin:2px 5px 5px 5px;line-height:23px;">
      Requset Info: {$requsetinfo}<br>
      Running Time: {$runtime}<br>
      Use Memory: {$memory}<br>
      <div>
        File:
        <ol style="padding: 0; margin:0">
          <foreach var="requsetfile" value="value">
            <li style="border-bottom:1px solid #EEE;font-size:14px;padding:0 12px">[$value]</li>
          </foreach>
        </ol>
      </div>
    </div>
  </div>
</div>