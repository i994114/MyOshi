<footer id="footer">
      Copyright <a href="index.php">WEBサービス部アウトプット</a>. All Rights Reserved.
    </footer>

    <script src="js/vendor/jquery-2.2.2.min.js"></script>
    <script>
      $(function(){
        var $ftr = $('#footer');
        if( window.innerHeight > $ftr.offset().top + $ftr.outerHeight() ){
          $ftr.attr({'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) +'px;' });
        }
      });

      //メッセージ表示
      var $jsShowMsg = $('#js-show-msg');
      var msg = $jsShowMsg.text();
      if (msg.replace(/^[\s　]+|[\s　]+$/g, "").length) {
        $jsShowMsg.slideToggle('slow');
        setTimeout(function(){ $jsShowMsg.slideToggle('slow'); }, 5000);
      }

      //画像ライブビュー
      var $dropArea = $('.area-drop');
      var $fileInput = $('.input-file');

      $dropArea.on('dragover', function(e){
        e.stopPropagation();
        e.preventDefault();
        $(this).css('border', '3px #ccc dashed');
      });

      $dropArea.on('dragleave', function(e) {
        e.stopPropagation();
        e.preventDefault();
        $(this).css('border', 'none');
      });

      $fileInput.on('change', function(e) {
        $dropArea.css('border', 'none');
        var file = this.files[0],      //ファイル取得
            $img = $(this).siblings('.prev-img'), //兄弟のimgを取得
            fileReader = new FileReader();  //ファイルを読み込みオブジェクト
        fileReader.onload = function(event) {
          $img.attr('src', event.target.result).show();
        }
        
        fileReader.readAsDataURL(file);
      });

      //カウントアップ
      var $countUp = $('#js-count'),
          $countView = $('#js-count-view');

      $countUp.on('keyup',function(e){
        $countView.html($(this).val().length);
      });

      //詳細情報画面用：画像ポップアップ
      $showMain = $('#js-show-main');
      $showSub = $('.js-show-sub');

      $showSub.on('click', function(e) {
        $showMain.attr('src',$(this).attr('src'));
      });
      
      //お気に入り
      var $like = $('.js-click-like') || null,
          likeProductID = $like.data('productid') || null;

      if (likeProductID !== undefined && likeProductID !== null) {
        $like.on('click',function(){
          $this = $(this);
          
          $.ajax({
            type: 'POST',
            url:'ajaxLike.php',
            data: {productId : likeProductID}
          }).done(function(){
            console.log('Ajax Success');
            $this.toggleClass('active');
          }).fail(function(){
            console.log('Ajax fail');
          });
        });
      }

    </script>
  </body>
</html>