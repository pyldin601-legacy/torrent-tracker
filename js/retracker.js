function fileexpl(hash,base) {
	var el=$(base).parent().find(".filecont"); //.$("#hash_"+hash);
	if(el.css("display")=='none') {
		$.get("info.php?hash="+hash, function (data) {
			el.html(data);
			el.css("display", "block");
		});
	} else {
		el.html("");
		el.css("display", "none");
	}
}

function toggle(el) {
	$(el).parent().toggleClass("closed");
}

function addEngine() {
	window.external.AddSearchProvider("http://tracker.tedirens.com/searchEngine.xml");
	return false;
}

$(document).ready(function() {
	//if(updater_enable) { setInterval("pageFetch()", 30000); }
});

function pageFetch() {
	$.get("update.php?after=" + unixtime, function(data) {
		unixtime = $(data).filter(".newtime").html();
		var el = $(data).find("tbody").html();
		if(el) {
			var data = $(".result").find("tbody").html();
			$(".result").find("tbody").html(el + data);
		}
	});
}

function autof() {
	var txt = $(".itext");
	//var lines = txt.val().replace(/(?!\[b\])(.+)\:(?!\[\/b\])(\s.*)/g, "[b]$1:[/b]$2");
	var trm = '';
	var lines = txt.val().split("\n");
	for(line in lines) {
		trm = lines[line].split(":");
		if(trm.length > 1) {
			if(trm[0].substr(0,3) != '[b]' && trm[0].substr(-4,3) != '[/b]' && trm[0].split(" ").length < 4) {
				trm[0] = '[b]' + trm[0] + '[/b]';
				lines[line] = trm.join(":");
			}
		}
	}
	txt.val(lines.join("\n"));
	//console.log(lines.join("\n"));
}

function testthis() {
	var resp = '';
	var str = $(".testthis").val();
	$.ajax( { url: "censor.php?q=" + str, async: false, success : function(text) { resp = text }} );

	if(resp == 'OK') {
		return true;
	} else {
		alert(resp);
		return false;
	}
}

function showfiles(el, id) {
	var dst = $(el).parent();
	dst.html("<img src='images/ajax.gif'> Загрузка...");
	$.get("contents.php?id=" + id, function(data) {
		dst.html(data);
	});
}

function delete_torrent(id) {
	var r_val = confirm("Удалить раздачу " + id + "?");
	if(r_val) {
		$.get("block.php?id="+id, function(data){
			window.location.reload();
		});
	}
}