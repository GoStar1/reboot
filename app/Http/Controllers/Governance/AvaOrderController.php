<?php

namespace App\Http\Controllers\Governance;

use App\Enums\Chain;
use App\Http\Controllers\Controller;
use App\Models\Order\AvaOrder;
use App\Services\SystemUtils;
use Illuminate\Http\Request;

class AvaOrderController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->input('keyword');
        $status = $request->input('status');
        $query = AvaOrder::getQuery();
        !SystemUtils::isNull($status) && $query->where('status', $status);
        if ($keyword) {
            if (strlen($keyword) == 66) {
                $query->where('list_id', $keyword);
                $query->orWhere('confirm_trans_hash', $keyword);
            } else if (strlen($keyword) == 42) {
                $query->where('taker', $keyword);
                $query->orWhere('seller', $keyword);
            }
        }
        $keyword && $query->where('status', $status);
        $list = $query->orderByDesc('id')->paginate(15)->toArray();
        return view('governance.ava_order.index', [
            'active_menu' => 'governance.ava_order',
            'title' => 'Ava Orders',
            'list' => $list,
            'keyword' => $keyword,
            'status' => $status,
            'status_dict' => AvaOrder::$status_dict,
        ]);
    }
}
