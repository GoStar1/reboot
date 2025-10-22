<?php $page_path = !empty($page_path) ? $page_path : request()->path(); ?>
@if (isset($pagination['total']))
  <ul class="pagination no-margin justify-content-end">
    @if ($pagination['prev_page_url'])
      <li class="page-item"><a class="page-link" href="/{{$page_path}}?{{http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] - 1]))}}">«</a></li>
    @else
      <li class="disabled page-item"><a class="page-link" href="javascript:;">«</a></li>
    @endif
    @if ($pagination['current_page'] - 5 > 1)
      <li class="page-item"><a class="page-link" href="/{{$page_path}}?{{http_build_query(array_merge($_GET, ['page' => 1]))}}">1</a></li>
      @if ($pagination['current_page'] - 5 > 2)
        <li class="disabled page-item"><a class="page-link" href="javascript:;">...</a></li>
      @endif
    @endif
    @for ($i = max($pagination['current_page'] - 5, 1), $l = $pagination['current_page']; $i < $l; $i ++)
      <li class="page-item"><a class="page-link" href="/{{$page_path}}?{{http_build_query(array_merge($_GET, ['page' => $i]))}}">{{$i}}</a></li>
    @endfor
    @if ($pagination['current_page'])
      <li class="active page-item"><a class="page-link" href="javascript:;">{{$pagination['current_page']}}</a></li>
    @endif
    @for ($i = $pagination['current_page'] + 1, $l = min($pagination['current_page'] + 5, $pagination['last_page']); $i <= $l; $i ++)
        <li class="page-item"><a class="page-link" href="/{{$page_path}}?{{http_build_query(array_merge($_GET, ['page' => $i]))}}">{{$i}}</a></li>
    @endfor
    @if ($pagination['last_page'] > $pagination['current_page'] + 5)
      @if ($pagination['last_page'] - 1 > $pagination['current_page'] + 5)
        <li class="disabled page-item"><a class="page-link" href="javascript:;">...</a></li>
      @endif
      <li class="page-item"><a class="page-link" href="/{{$page_path}}?{{http_build_query(array_merge($_GET, ['page' => $pagination['last_page']]))}}">{{$pagination['last_page']}}</a></li>
    @endif
    @if ($pagination['next_page_url'])
      <li class="page-item"><a class="page-link" href="/{{$page_path}}?{{http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] + 1]))}}">»</a></li>
    @else
      <li class="disabled page-item"><a class="page-link" href="javascript:;">»</a></li>
    @endif
  </ul>
  <div class="no-margin" style="padding-top:8px;">
    <span class="text-muted float-right">total {{$pagination['total']}} record</span>&nbsp;&nbsp;&nbsp;&nbsp;
  </div>
@elseif (isset($pagination['prev_page_url']))
  <ul class="pager">
    @if ($pagination['prev_page_url'])
      <li class="previous"><a class="page-link" href="/{{$page_path}}?{{http_build_query(array_merge($_GET, ['page' => !empty($pagination['prev_page']) ? $pagination['prev_page'] : ($pagination['current_page'] - 1)]))}}">«&nbsp;上一页</a></li>
    @else
      <li class="previous disabled"><a class="page-link" href="javascript:;">«&nbsp;上一页</a></li>
    @endif
    @if ($pagination['next_page_url'])
      <li class="next"><a class="page-link" href="/{{$page_path}}?{{http_build_query(array_merge($_GET, ['page' => !empty($pagination['next_page']) ? $pagination['next_page'] : ($pagination['current_page'] + 1)]))}}">下一页&nbsp;»</a></li>
    @else
      <li class="next disabled"><a class="page-link" href="javascript:;">«&nbsp;下一页</a></li>
    @endif
  </ul>
@endif
