function date(format,timestamp){var that=this,jsdate,f,txt_words=['Sun','Mon','Tues','Wednes','Thurs','Fri','Satur','January','February','March','April','May','June','July','August','September','October','November','December'],formatChr=/\\?(.?)/gi,formatChrCb=function(t,s){return f[t]?f[t]():s},_pad=function(n,c){n=String(n);while(n.length<c){n='0'+n}return n};f={d:function(){return _pad(f.j(),2)},D:function(){return f.l().slice(0,3)},j:function(){return jsdate.getDate()},l:function(){return txt_words[f.w()]+'day'},N:function(){return f.w()||7},S:function(){var j=f.j(),i=j%10;if(i<=3&&parseInt((j%100)/10,10)==1){i=0}return['st','nd','rd'][i-1]||'th'},w:function(){return jsdate.getDay()},z:function(){var a=new Date(f.Y(),f.n()-1,f.j()),b=new Date(f.Y(),0,1);return Math.round((a-b)/864e5)},W:function(){var a=new Date(f.Y(),f.n()-1,f.j()-f.N()+3),b=new Date(a.getFullYear(),0,4);return _pad(1+Math.round((a-b)/864e5/7),2)},F:function(){return txt_words[6+f.n()]},m:function(){return _pad(f.n(),2)},M:function(){return f.F().slice(0,3)},n:function(){return jsdate.getMonth()+1},t:function(){return(new Date(f.Y(),f.n(),0)).getDate()},L:function(){var j=f.Y();return j%4===0&j%100!==0|j%400===0},o:function(){var n=f.n(),W=f.W(),Y=f.Y();return Y+(n===12&&W<9?1:n===1&&W>9?-1:0)},Y:function(){return jsdate.getFullYear()},y:function(){return f.Y().toString().slice(-2)},a:function(){return jsdate.getHours()>11?'pm':'am'},A:function(){return f.a().toUpperCase()},B:function(){var H=jsdate.getUTCHours()*36e2,i=jsdate.getUTCMinutes()*60,s=jsdate.getUTCSeconds();return _pad(Math.floor((H+i+s+36e2)/86.4)%1e3,3)},g:function(){return f.G()%12||12},G:function(){return jsdate.getHours()},h:function(){return _pad(f.g(),2)},H:function(){return _pad(f.G(),2)},i:function(){return _pad(jsdate.getMinutes(),2)},s:function(){return _pad(jsdate.getSeconds(),2)},u:function(){return _pad(jsdate.getMilliseconds()*1000,6)},e:function(){throw'Not supported (see source code of date() for timezone on how to add support)';},I:function(){var a=new Date(f.Y(),0),c=Date.UTC(f.Y(),0),b=new Date(f.Y(),6),d=Date.UTC(f.Y(),6);return((a-c)!==(b-d))?1:0},O:function(){var tzo=jsdate.getTimezoneOffset(),a=Math.abs(tzo);return(tzo>0?'-':'+')+_pad(Math.floor(a/60)*100+a%60,4)},P:function(){var O=f.O();return(O.substr(0,3)+':'+O.substr(3,2))},T:function(){return'UTC'},Z:function(){return-jsdate.getTimezoneOffset()*60},c:function(){return'Y-m-d\\TH:i:sP'.replace(formatChr,formatChrCb)},r:function(){return'D, d M Y H:i:s O'.replace(formatChr,formatChrCb)},U:function(){return jsdate/1000|0}};this.date=function(format,timestamp){that=this;jsdate=(timestamp===undefined?new Date():(timestamp instanceof Date)?new Date(timestamp):new Date(timestamp*1000));return format.replace(formatChr,formatChrCb)};return this.date(format,timestamp)}
function mktime(){var d=new Date(),r=arguments,i=0,e=['Hours','Minutes','Seconds','Month','Date','FullYear'];for(i=0;i<e.length;i++){if(typeof r[i]==='undefined'){r[i]=d['get'+e[i]]();r[i]+=(i===3)}else{r[i]=parseInt(r[i],10);if(isNaN(r[i])){return false}}}r[5]+=(r[5]>=0?(r[5]<=69?2e3:(r[5]<=100?1900:0)):0);d.setFullYear(r[5],r[3]-1,r[4]);d.setHours(r[0],r[1],r[2]);return(d.getTime()/1e3>>0)-(d.getTime()<0)}



function blViewDatesEvents(objBtn,loc_id) {
	var btn = jQuery(objBtn);

	if(btn.prop('disabled')) {
		return;
	}

	var show = function(results) {

		var eDiv = document.getElementById('viewDates');
		
		if(eDiv) {
			jQuery(eDiv).remove();
		}
		var divView = jQuery('<div id="viewDates" title="Message">');
		var apHtml ='', item, time, tmpl;
		
		if( typeof(divView.dialog) === 'function' ) {
			jQuery(document.body).append(divView);
			tmpl = '<br/><div>Date: {date}<br/>Time: {time}</div><hr/>';
		} else {
			tmpl = 'Date: {date}\nTime: {time}\n-------------------------------------\n';
		}
		for(var i in results) {

			item = results[i];
			time = mktime(0,0,0,item.month,item.day,item.year);

			apHtml += tmpl.replace('{date}',date('F d, Y',time)).replace('{time}',item.stime+' - '+item.etime);
		}

		if( typeof(divView.dialog) === 'function' ) {
			if(apHtml.length > 0) {
				divView.html(apHtml);
			} else {
				divView.html('Not found Event Date and Times');
			}
			divView.dialog({modal: true});
		} else {
			if(apHtml.length > 0) {
				alert(apHtml);
			} else {
				alert('Not found Event Date and Times');
			}
		}
	};

	if(typeof(loc_id) === 'number' && loc_id%1 === 0) {
		
		var oldTxt = btn.text();
		btn.prop('disabled',true).attr('disabled', 'true').text('Loading...');

		jQuery.ajax({
			type: 'post',
			data: {
				action: 'getlocationdates',
				location_id: loc_id
			},
			url: ajaxurl,
			dataType: 'json',
			success: function(response) {
				if(response.status == 'OK') {
					
					if(response.results.length > 0) {
						show(response.results);
					}
				}
				btn.prop('disabled',false).removeAttr('disabled').text(oldTxt);
			}
		});
	} else {
		var results = jQuery.parseJSON(loc_id);
		show(results);
	}
}