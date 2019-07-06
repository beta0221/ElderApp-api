
// $.validator.setDefaults({
    
// });
$().ready(function() {
    $("#validate_form").validate();
});

jQuery.extend(jQuery.validator.messages, {
    required: "這是必填欄位",
    email: "email格式錯誤",
    maxlength: jQuery.validator.format("最多{0}碼"),
    minlength: jQuery.validator.format("最少{0}碼"),
    max: jQuery.validator.format("最多為{0}"),
    min: jQuery.validator.format("最少為{0}"),
  });