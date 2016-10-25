<?php
class UserCenterController extends Controller {
    function index() {
        $this->display();
    }

    function getPage() {
        if(I('page') && I('type')) {
            $filename = __ROOT__ . APP_NAME . '/' . UrlParse::$model . '/Tpl/UserCenter/Page/' . I('page') . '.' . (I('type') == 'js' ? 'js' : 'page');
            if(file_exists($filename)) {
                echo file_get_contents($filename);
            }
        }
    }

    function getUserInfo() {
        echo 'getUserInfo';
    }
}