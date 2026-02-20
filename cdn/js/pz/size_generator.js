$(function()
{
	var classObjs = [],
		 loadCtr = 0;
	
	function processText()
	{
		classObjs = [];
		loadCtr = 0;
		
		var inputVal = $("#inputText").val(),
			inputHtml = $("<div>" + inputVal + "</div>"),
			dataSrcElems = inputHtml.find("img[data-src]"),
			classPrepend = $("#prependText").val(),
			i = 0,
			j = 0;
		
		for(i = 0; i < dataSrcElems.length; i++)
		{
			console.log(i);
			var dataSrcElem = dataSrcElems.eq(i),
				url = dataSrcElem.data("src"),
				classObj = { width:null, height:null },
				folderSplit = url.split("/"),
				fileNameSplit = folderSplit[folderSplit.length - 1].split(".")[0];
				 
			classObj.name = classPrepend + fileNameSplit;
			classObj.url = url;
			dataSrcElem.addClass(classObj.name);
			
			if(indexOfClass(classObj.name) == -1)
			{
				classObjs.push(classObj);
			}	
		}
		
		loadImages();
		
		$("#outputHTMLText").val(inputHtml.html())
		
	}
	
	function loadImages()
	{
		for(var i = 0; i < classObjs.length; i++)
		{
			console.log("I IS: " + classObjs[i].url);
			var image = $("<img>").one("load", {index:i}, onImageLoad).one("error", {index:i}, onImageError).attr("src", classObjs[i].url);	
		}
	}
	
	function onImageError(e)
	{
		
		var index = e.data.index;
		console.log("ERROR " + index);
	}
	
	function onImageLoad(e)
	{
		console.log("LOAD");
		var index = e.data.index,
			 target = $(e.target);
		
		classObjs[index].width = target[0].naturalWidth;
		classObjs[index].height = target[0].naturalHeight;
		
		
		console.log(classObjs[index].name + " w: " + classObjs[index].width + " h: " + classObjs[index].height);
		
		loadCtr++;
		
		if(loadCtr == classObjs.length)
		{
			console.log("LOAD COMPLETE!");	
			generateClass();
		}
		
	}
	
	function generateClass()
	{
		
		var classVal = "";
		for(var i = 0; i < classObjs.length; i++)
		{
			var classObj = classObjs[i]
			classVal += "." + classObj.name + "\n{\nwidth: " + classObj.width + "px;\nheight: " + classObj.height + "px\n}\n"; 		
		}
		
		$("#outputCSSText").val(classVal);
	}
	
	function indexOfClass(className)
	{
		var classIndex = -1;
		for(var i = 0; i < classObjs.length; i++)
		{
			if(className == classObjs[i].name)
			{
				classIndex = i;
				i = classObjs.length;	
			}
		}
		
		return classIndex;
		
	}
	
	$("#processButton").on("click", processText);
	
	
	
});