<?php

namespace App\Http\Controllers\Upload;


use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class ImageController extends Controller
{
    public function store()
    {
        $_FILES = array_values($_FILES);
        $tmp_file = $_FILES[0]['tmp_name'];
        $type = $_FILES[0]['type'];
        $image = @getimagesize($tmp_file, $info);
        Log::debug('info', $info);
        if (!$image) {
            return $this->error(20133, trans('upload.img_format_error'));//'图片格式错误'
        }
        $uploaddir = public_path('/image/');
        $name = md5_file($tmp_file);
        $ext = '';
        $type = strtolower($type);
        if ($type === 'image/jpeg') {
            $ext = '.jpeg';
        } else if ($type === 'image/png') {
            $ext = '.png';
        } else if ($type === 'image/jpg') {
            $ext = '.jpg';
        } else if ($type === 'image/gif') {
            $ext = '.gif';
        } else if ($type === 'image/pjpeg') {
            $ext = '.jpeg';
        }
        $name .= $ext;
        $uploadfile = $uploaddir . $name;
        $ret = move_uploaded_file($tmp_file, $uploadfile);
        return $ret ? $this->success([
            'url' => config('app.url') . '/image/' . $name,
        ]) : $this->error(20131, '');
    }
}
