<?php
if (isset($_FILES['upfile']['error']) && is_array($_FILES['upfile']['error'])) {
    foreach ($_FILES['upfile']['error'] as $k => $error) {
        try {
            // 更に配列がネストしていれば不正とする
            if (!is_int($error)) {
                throw new RuntimeException("[{$k}] パラメータが不正です");
            }
             
            // $_FILES['upfile']['error'] の値を確認
            switch ($error) {
                case UPLOAD_ERR_OK: // OK
                    break;
                case UPLOAD_ERR_NO_FILE:   // ファイル未選択
                    continue 2;
                case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズ超過
                case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過
                    throw new RuntimeException("[{$k}] ファイルサイズが大きすぎます");
                default:
                    throw new RuntimeException('その他のエラーが発生しました');
            }
 
            // $_FILES['upfile']['mime']の値はブラウザ側で偽装可能なので
            // MIMEタイプを自前でチェックする
            if (!$info = @getimagesize($_FILES['upfile']['tmp_name'][$k])) {
                throw new RuntimeException("[{$k}] 有効な画像ファイルを指定してください");
            }
            //$info[2]はIMAGETYPE_XXX定数
            // 1 IMAGETYPE_GIF
            // 2 IMAGETYPE_JPEG
            // 3 IMAGETYPE_PNG
            if (!in_array($info[2], [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
                throw new RuntimeException("[{$k}] 未対応の画像形式です");
            }
             
            // 横幅330に収まるようにサイズを調整する
            //$info[0]は横幅、$info[1]は高さ
            if($info[0] > 330){
                $dst_width = 330;
                $dst_height = ($info[1] * 330 / max($info[0],1) );
            }else{
                $dst_width = $info[0];
                $dst_height = $info[1];
            }
             
            // 元画像リソースを生成
            if($info[2] == IMAGETYPE_GIF){
                $src = imagecreatefromgif($_FILES['upfile']['tmp_name'][$k]);
            }elseif($info[2] == IMAGETYPE_JPEG){
                $src = imagecreatefromjpeg($_FILES['upfile']['tmp_name'][$k]);
            }elseif($info[2] == IMAGETYPE_PNG){
                $src = imagecreatefrompng($_FILES['upfile']['tmp_name'][$k]);
            }
             
            if(!$src){
                throw new RuntimeException("[{$k}] 画像リソースの生成に失敗しました");
            }
             
            // リサンプリング先画像リソースを生成する
            $dst = imagecreatetruecolor($dst_width, $dst_height);
             
            // getimagesize関数で得られた情報も利用してリサンプリングを行う
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $dst_width, $dst_height, $info[0], $info[1]);
 
            // 保存先ディレクトリ、ファイル名
            $save_dir = "./images/";
            $filename = date("YmdHis")."_{$k}".image_type_to_extension($info[2]);
             
            // 画像の保存
            if($info[2] == IMAGETYPE_GIF){
                $save_result = imagegif($dst, $save_dir.$filename);
            }elseif($info[2] == IMAGETYPE_JPEG){
                $save_result = imagejpeg($dst, $save_dir.$filename);
            }elseif($info[2] == IMAGETYPE_PNG){
                $save_result = imagepng($dst, $save_dir.$filename);
            }
             
            if(!$save_result){
                throw new RuntimeException("[{$k}] ファイル保存時にエラーが発生しました");
            }
 
            // 向き修正、exif情報削除
            $org_imagick = new Imagick($_FILES['upfile']['tmp_name'][$k]);
            $org_orientation = $org_imagick->getImageOrientation();
             
            $imagick_outimage = new Imagick($save_dir.$filename);
             
            switch ($org_orientation) {
                case 2: // Mirror horizontal
                    $imagick_outimage->flopImage();
                    break;
                case 3: // Rotate 180
                    $imagick_outimage->rotateImage('#000000', 180);
                    break;
                case 4: // Mirror vertical
                    $imagick_outimage->flipImage();
                    break;
                case 5: // Mirror horizontal and rotate 270
                    $imagick_outimage->flopImage();
                    $imagick_outimage->rotateImage('#000000', 270);
                    break;
                case 6: // Rotate 90
                    $imagick_outimage->rotateImage('#000000', 90);
                    break;
                case 7: // Mirror horizontal and rotate 90
                    $imagick_outimage->flopImage();
                    $imagick_outimage->rotateImage('#000000', 90);
                    break;
                case 8: // Rotate 270
                    $imagick_outimage->rotateImage('#000000', 270);
                    break;
            }
             
            $imagick_outimage->stripimage();
            $imagick_outimage->writeimage($save_dir.$filename);
 
            $msg = ['green', 'ファイルは正常にアップロードされました'];
             
        } catch (RuntimeException $e) {
            $msg = ['red', $e->getMessage()];
        }
    }
}

echo($msg[1])
?>

