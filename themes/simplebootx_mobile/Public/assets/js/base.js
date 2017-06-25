/**
 * Created by user on 2017/6/13.
 */
function AjaxGet(url, onsuccess, onerror) {
    $.ajax({
        url: url,
        dataType: "json",
        contentType: 'application/json;charset=utf-8',
        type: "get",
        beforeSend: function(request) {
            //request.setRequestHeader("ticket",ticket);
        },
        error: function(xhr, status, error) {
            if (onerror)
                onerror(error);
        },
        success: function(data, status, xhr) {
            onsuccess(data);
        }
    });
};

function AjaxPost(url, data, tag, onsuccess, onerror) {
    $.ajax({
        url: url,
        dataType: "json",
        contentType: 'application/json; charset=utf-8',
        type: "post",
        data: JSON.stringify(data),
        beforeSend: function(request) {
            //request.setRequestHeader("ticket", myapp.ticket);
        },
        error: function(xhr, status, error) {
            if (onerror)
                onerror(error);
        },
        success: function(data, status, xhr) {
            if (onsuccess) onsuccess(data, tag);
        }
    });
};

//�Զ��嵥ѡ��ť
$('.my-radio').on('click', function(){
    $('.my-radio').find('.icon').removeClass('icon-choose active').addClass('icon-ring');
    $(this).find('.icon').removeClass('icon-ring').addClass('icon-choose active');
});

//�Զ����ѡ��ť
$('.my-checkbox').on('click', function(){
   if($(this).find('.icon').hasClass('icon-squre')) {
       $(this).find('.icon').removeClass('icon-squre').addClass('icon-check-squre');
   }else{
       $(this).find('.icon').removeClass('icon-check-squre').addClass('icon-squre');
   }
});

//��ʾ��ر�
$('.close').on('click', function(){
   $('.alert-mask').hide();
});

//tab�л�
$(function(){
    $('.tab-item').on('click', function(){
        $(this).addClass('active').siblings().removeClass('active');
        $('.tab-content').hide();
        $('.tab-content').eq($(this).index()).show();
    });
});