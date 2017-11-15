<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Tabs;
use App\Users;
use App\User;

class HomeController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        //$users = User::userWithTeams()->toArray();
        //dd($users);
        $data = Tabs::allTabs();
        $allTabs = Tabs::tabsData('All');
        $colDetails = Users::getColumnDetails();
        $filters = Users::getFiltrableData('All');

        return view('home', array(
            'activeTab'=>'All',
            'tabs' => $data,
            'allTabs' => $allTabs->toArray(),
            'columnDetails' => $colDetails,
            'filters' => $filters));
    }

    public function filterTab($tab) {
        $data = Tabs::allTabs();
        $allTabs = Tabs::tabsData($tab);
        $colDetails = Users::getColumnDetails();
        $filters = Users::getFiltrableData('All');

        return view('home', array(
            'activeTab'=>$tab,
            'tabs' => $data,
            'allTabs' => $allTabs->toArray(),
            'columnDetails' => $colDetails,
            'filters' => $filters));
    }

}
