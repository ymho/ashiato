<script>
    function input_check(){
 
 //ファイル数が6コ以上の場合はNG
 var file_num_flg = 0;
 var fileList = document.getElementById('select_file').files;
 if(fileList.length > 6){
     file_num_flg = 1;
 }
  
 //ファイルサイズが3MBよりデカい場合はNG
 var file_size_flg = 0;
 for(i=0; i<fileList.length; i++){ if(fileList[i].size > 3000000){
         file_size_flg = 1;
     }
 }
  
 if(file_num_flg > 0){
     window.alert('添付ファイル数が上限を超えています');
 }else if(file_size_flg > 0){
     window.alert('投稿する画像のファイルサイズは3MB以下にしてください');
 }else{
     document.form1.submit();
 }
}
</script>
<form action="./received.php" name="form1" method="POST" enctype="multipart/form-data">
    写真の説明<textarea name="description" cols="40" rows="2"></textarea>
    投稿<textarea name="text" cols="40" rows="2"></textarea>
    写真<input type="file" id="select_file" accept="image/jpeg, image/gif, image/png" name="upfile[]" multiple="multiple">
    <a href="javascript:void(0)" onclick="input_check()">アップロードする</a>
</form>