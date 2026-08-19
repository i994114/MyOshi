$(function(){

  //フッター位置調整
  var $ftr = $('#footer');

	if ($ftr.length) {
		if (window.innerHeight > $ftr.offset().top + $ftr.outerHeight()) {
			$ftr.attr({
				'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) + 'px;'
			});
		}
	}

  //イベント時間未定時の開始時間・終了時間の入力欄を非表示にする
  var $timeUndecided = $('.js-time-undecided');
  var $timeSelect = $('.js-start-time, .js-end-time');

  $timeUndecided.on('change', function() {
    var isUndecided = $(this).prop('checked');

    $timeSelect
      .prop('disabled', isUndecided)
      .toggle(!isUndecided);
  });

  //メッセージ表示
  var $jsShowMsg = $('#js-show-msg');
  var msg = $jsShowMsg.text();

  if (msg.replace(/^[\s]+|[\s]+$/g, "").length) {
    $jsShowMsg.slideToggle('slow');

    setTimeout(function(){
      $jsShowMsg.slideToggle('slow');
    }, 5000);
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

    var file = this.files[0],
        $img = $(this).siblings('.prev-img'),
        fileReader = new FileReader();

    fileReader.onload = function(event) {
      $img.attr('src', event.target.result).show();
    }

    fileReader.readAsDataURL(file);
  });

  //カウントアップ
  var $countUp = $('#js-count'),
      $countView = $('#js-count-view');

  $countUp.on('keyup', function(e){
    $countView.html($(this).val().length);
  });

  //詳細情報画面用：画像ポップアップ
  var $showMain = $('#js-show-main');
  var $showSub = $('.js-show-sub');

  $showSub.on('click', function(e) {
    $showMain.attr('src', $(this).attr('src'));
  });

  //お気に入り
  var $like = $('.js-click-like'),
      likeEventID = $like.data('eventid');

  if (likeEventID !== undefined && likeEventID !== null) {
    $like.on('click', function(){
      var $this = $(this);

      $.ajax({
        type: 'POST',
        url: 'ajaxLike.php',
        data: {eventId: likeEventID}
      }).done(function(){

        var likeCount = Number($this.find('.js-like-count').text());

        if ($this.hasClass('active')) {
          likeCount = likeCount - 1;
        } else {
          likeCount = likeCount + 1;
        }

        $this.find('.js-like-count').text(likeCount);
        $this.toggleClass('active');

      }).fail(function(){
        console.log('Ajax fail');
      });
    });
  }

  //参加/不参加
  var $participant = $('.js-click-event-participant'),
      participantEventID = $participant.data('eventid');

  if (participantEventID !== undefined && participantEventID !== null) {
    $participant.on('click', function(){
      var $this = $(this);

      $.ajax({
        type: 'POST',
        url: 'ajaxParticipant.php',
        data: {eventId: participantEventID}
      }).done(function(){

        if ($this.val() === '参加する') {
          $this.val('参加取消');
        } else {
          $this.val('参加する');
        }

      }).fail(function(){
        console.log('Ajax fail');
      });
    });
  }

	//掲示板を最新メッセージまでスクロール
	var $scrollBottom = $('#js-scroll-bottom');

	if ($scrollBottom.length) {
		$scrollBottom.animate({scrollTop: $('#js-scroll-bottom')[0].scrollHeight}, 'fast');
	}

});