//Provide API requests with centralized error handling to simplify code
//and support retrying of requests
var APIRequestPool=[];

if(typeof appstate!="object")
  appstate={};
appstate.requestCounter=0;

//when this is set to true, don't whine about API errors
//all requests will be canceled and their error() events fired upon unload!
var isUnloading=false;

window.addEventListener("beforeunload",function() {
  console.log("unloading page!");
  isUnloading=true;
});

//do an API request
//param.ignoreNetworkException=true: do not report to the user that the request failed on network level, but still call s/f/a handlers
//param.ignoreAPIException=true: do not prompt the user for retry of request, still call s/f/a handlers
function doAPIRequest(target,params,success,fail,always,progress) {
  var logstr="";
  var queryUrl=appconfig.apiurl+"?action="+target;
  var reqId=appstate.requestCounter++;
  for(var key in params) {
  	if(key=="fileUpload" || key=="json_input")
  		continue;
    logstr+=key+"="+params[key]+",";
    queryUrl+="&"+key+"="+encodeURIComponent(params[key]);
  }
  logstr=logstr.substring(0,logstr.length-1);
  console.glog("api","Submitting request to API "+target+" ("+logstr+") with ID "+reqId+", raw query is "+queryUrl);
  if(params.sync)
    var request=$.ajax({
      type:"GET",
      url:queryUrl,
      dataType:"json",
      async:false,
    });
  else {
  	if(params.fileUpload) {
  		var fileInput=params.fileUpload.files[0];
  		var formdata=new FormData();
  		var reader=new FileReader();
  		reader.readAsDataURL(fileInput);
  		formdata.append("api_fileupload",fileInput);
  		if(params.json_input)
  			formdata.append("json_input",params.json_input);
  		var request=$.ajax({
  			type:"POST",
  			url:queryUrl,
  			data:formdata,
  			processData:false,
  			contentType:false,
  			dataType:"json"
  		});
  	} else {
  		if(params.json_input) {
  			var formdata=new FormData();
  			formdata.append("json_input",params.json_input);
  			var request=$.ajax({
  			type:"POST",
  			url:queryUrl,
  			data:formdata,
  			processData:false,
  			contentType:false,
  			dataType:"json"
  			});
  		} else
		    var request=$.getJSON(queryUrl);
	  }
  }
  request._url=queryUrl;
  APIRequestPool.push(request);
  $("#poollen").html(APIRequestPool.length);
  request.done(function(data) {
      console.glog("api","Request #"+reqId+" to API "+target+" ("+logstr+") returned OK on network level, data object is:");
      console.glog("api",data);
      if(!data.status || (data.status!="ok" && !params.ignoreAPIException)) {
        if(!data.message)
          data.message="";
        console.gerror("api","Request #"+reqId+" to API "+target+" ("+logstr+") returned error on API level: '"+data.message+"'");
        //See if the user wants to retry, don't fail silently
        if(confirm(sprintf(_("apierror_confirm"),target)))
          doAPIRequest(target,params,success,fail,always);
        else {
          if(typeof(fail)=="function") {
            console.glog("api","Calling the 'fail' handler of API request #"+reqId);
            fail();
          }
        }
        return;
      }
      if(typeof(success)=="function") {
        console.glog("api","Calling the 'success' handler of API request #"+reqId);
        success(data);
      }
    }).
    fail(function() {
      if(isUnloading) {
        console.gerror("api","Request #"+reqId+" to API "+target+" ("+logstr+") failed because of page unload!");
        return;
      }
      console.gerror("api","Request #"+reqId+" to API "+target+" ("+logstr+") failed on network level");
      if(params.ignoreNetworkException && params.ignoreNetworkException==true) {
        console.glog("api","Not showing failure to user, override specified");
      } else {
        if(confirm(sprintf(_("apierror_confirm"),target))) {
          doAPIRequest(target,params,success,fail,always);
          return; //the request may succeed on retry, so return and do not fire the supplied fail-handler
        }
      }
      if(typeof(fail)=="function") {
        console.glog("api","Calling the 'fail' handler of API request #"+reqId);
        fail();
      }
    }).
    always(function(a,b,c) {
      if(typeof(always)=="function") {
        console.glog("api","Calling the 'always' handler of API request #"+reqId);
        always();
      }
      //Determine if a or c is the XHR object.
      //Why in fucks name has jQuery decided on jqXHR.always(function(data|jqXHR, textStatus, jqXHR|errorThrown) { }); ?!?!
      //If someone knows someone who wrote this, please have someone check his mental health.
      var xhr=(typeof c=="string")?a:c;
      var k=APIRequestPool.indexOf(xhr);
      if(k==-1) {
        console.gerror("api","Request",reqId," has no pool entry!");
        return;
      }
      APIRequestPool.splice(k,1);
      $("#poollen").html(APIRequestPool.length);
    }).progress(function(e) {
      if(typeof(progress)=="function") {
        console.glog("api","calling the 'progress' handler of API request #"+reqId);
        progress();
      }
    });
  
}

//abort all running API requests
function cancelAllRequests() {
  APIRequestPool.forEach(function(e) {
    e.abort();
  });
}
