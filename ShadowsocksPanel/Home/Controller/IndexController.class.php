<?php
class IndexController extends Controller {
    function index() {
        $this->login = false;
        $this->display();
    }
}
