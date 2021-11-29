    console.log('service start');
    apiurl = window.location.origin+'/oasys/api';

    function sendRequest(url, method, data) {
        var d = $.Deferred();
    
        method = method || "GET";
    
                $.ajax(url, 
                {
                    method: method || "GET",
                    data: data,
                    cache: false,
                }).done(function(result) {
                d.resolve(method === "GET" ? result : result);
        
                // var type = (result.status == "success" ? "success" : "error"),
                // text = result.message;
                // time = (result.status == "success" ? 2000 : 5000)
                
                // DevExpress.ui.notify(text, type, time);
                console.log(result);
            }).fail(function(xhr) {
                d.reject(xhr.responseJSON ? xhr.responseJSON.Message : xhr.statusText);
            });
    
        return d.promise();
    }
 