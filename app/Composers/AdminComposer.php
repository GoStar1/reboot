<?php

namespace App\Composers;

use App\Enums\Chain;
use Illuminate\Contracts\View\View;

class AdminComposer
{
    /**
     * Bind data to the view.
     *
     * @param View $view
     * @return void
     */
    public function compose(View $view): void
    {
        $this->_composeAdminInfo($view);
        $this->_composeSidebarMenu($view);
    }

    /**
     * The admin info.
     *
     * @param View $view
     * @return void
     */
    private function _composeAdminInfo(View $view): void
    {
        $view->with('admin_blank_avatar', '/images/avatar5.png');
    }

    /**
     * The main sidebar menu.
     *
     * @param View $view
     * @return void
     */
    private function _composeSidebarMenu(View $view): void
    {
        $sidebar_menu = [];
        $sidebar_menu['user'] = [
            'alias' => 'Admin', 'icon' => 'user-alt',
            'children' => [
                'changePassword' => ['alias' => 'ChangePassword', 'url' => route('password.update')],
                'userLogin' => ['alias' => 'User Login', 'url' => route('user.history')],
                'operations' => ['alias' => 'Operations', 'url' => route('user.operation')],
            ],
        ];
        $sidebar_menu['dashboard'] = [
            'alias' => 'Dashboard', 'icon' => 'tachometer-alt',
            'children' => [
                'main' => ['alias' => 'Main', 'url' => '/dashboard/main'],
            ],
        ];
        $sidebar_menu['governance'] = [
            'alias' => 'Governance', 'icon' => 'book',
            'children' => [
                'account' => ['alias' => 'Account', 'url' => '/governance/account'],
                'token' => ['alias' => 'ERC20 Token', 'url' => '/governance/token'],
                'template' => ['alias' => 'Template', 'url' => '/governance/template'],
                'rpc' => ['alias' => 'Node Rpc', 'url' => '/governance/rpc'],
//                'event' => ['alias' => 'Event', 'url' => '/governance/event'],
                'taskData' => ['alias' => 'Task Data', 'url' => '/governance/task-data'],
                'task' => ['alias' => 'Task', 'url' => '/governance/task'],
                'taskTrans' => ['alias' => 'Task Trans', 'url' => '/governance/task-trans'],
                'ava_order' => ['alias' => 'Ava Orders', 'url' => route('ava-order.index')],
            ],
        ];
        $sidebar_menu['operation'] = [
            'alias' => 'Operation', 'icon' => 'wrench',
            'children' => []
        ];
        if (\App::environment('local')) {
            $sidebar_menu['template'] = [
                'alias' => 'template', 'icon' => 'file', 'url' => '/admin-lte/index.html', 'target' => '_blank',
            ];
        }
        $view->with('sidebar_menu', $sidebar_menu);
    }
}
