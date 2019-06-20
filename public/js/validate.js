
$.validator.setDefaults({
    // submitHandler: function() {
    //     $('body').append('<div class="preloader js-preloader flex-center"><div class="dots"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div></div>');
    //     $('.js-preloader').preloadinator();
    //     // $("#validate_form").submit();
    // }
});
$().ready(function() {
    $("#validate_form").validate();
});

function loadingFirst(){
    
}
jQuery.extend(jQuery.validator.messages, {
    required: "這是必填欄位",
    email: "email格式錯誤",
    maxlength: jQuery.validator.format("最多{0}碼"),
    minlength: jQuery.validator.format("最少{0}碼"),
    max: jQuery.validator.format("最多為{0}"),
    min: jQuery.validator.format("最少為{0}"),
  });