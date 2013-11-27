<h2 class="sectionTitle">Create/Update Notes</h2>

<div id="successdiv" class="noDisplay alert alert-success">
<button type="button" class="close" data-dismiss="alert">&times;</button>Note saved successfully.</div>

<div id="errordiv" class="noDisplay alert alert-error">
<button type="button" class="close" data-dismiss="alert">&times;</button>An error occurred while trying to save the note. Please try again.</div>


<p>Use this form to create notes for special circumstances that may occur on certain days.</p>
<p>To modify an existing note, select the date below.</p>
<form id="addNotes" enctype="multipart/form-data" action="/events/manage/php/post_notes.php" method="post">

	<div class="input-append date" id="date-picker" data-date-format="mm/dd/yyyy">
        <input type="text" class="input-small" placeholder="Select date" name="date-input" id="date-input" required>
        <span class="add-on"><i class="icon-calendar" style="color:#333;"></i></span>
        
    </div>
    <span class="inlineError" id="dateRequired"></span>

	<div data-target="#editor" data-role="editor-toolbar" class="btn-toolbar">
	  <div class="btn-group">
	    <a title="" data-edit="bold" class="btn" data-original-title="Bold"><i class="icon-bold"></i></a>
	    <a title="" data-edit="italic" class="btn" data-original-title="Italic"><i class="icon-italic"></i></a>
	    <a title="" data-edit="strikethrough" class="btn" data-original-title="Strikethrough"><i class="icon-strikethrough"></i></a>
	    <a title="" data-edit="underline" class="btn" data-original-title="Underline"><i class="icon-underline"></i></a>
	  </div>
	  <div class="btn-group">
	    <a title="" data-edit="insertunorderedlist" class="btn" data-original-title="Bullet list"><i class="icon-list-ul"></i></a>
	    <a title="" data-edit="insertorderedlist" class="btn" data-original-title="Number list"><i class="icon-list-ol"></i></a>
	    <a title="" data-edit="outdent" class="btn" data-original-title="Reduce indent"><i class="icon-indent-left"></i></a>
	    <a title="" data-edit="indent" class="btn" data-original-title="Indent"><i class="icon-indent-right"></i></a>
	  </div>
	  <div class="btn-group">
	    <a title="" data-edit="justifyleft" class="btn" data-original-title="Align Left"><i class="icon-align-left"></i></a>
	    <a title="" data-edit="justifycenter" class="btn" data-original-title="Center"><i class="icon-align-center"></i></a>
	    <a title="" data-edit="justifyright" class="btn" data-original-title="Align Right"><i class="icon-align-right"></i></a>
	    <a title="" data-edit="justifyfull" class="btn" data-original-title="Justify"><i class="icon-align-justify"></i></a>
	  </div>
	  <div class="btn-group">
		<a title="" data-toggle="dropdown" class="btn dropdown-toggle" data-original-title="Hyperlink"><i class="icon-link"></i></a>
	    <div class="dropdown-menu input-append">
		    <input type="text" data-edit="createLink" placeholder="URL" class="span2" />
		    <button type="button" class="btn">Add</button>
		</div>
	    <a title="" data-edit="unlink" class="btn" data-original-title="Remove Hyperlink"><i class="icon-cut"></i></a>
	  </div>
	  <div class="btn-group">
	    <a id="pictureBtn" title="" class="btn" data-original-title="Insert picture (or just drag &amp; drop)"><i class="icon-picture"></i></a>
	    <input type="file" data-edit="insertImage" data-target="#pictureBtn" data-role="magic-overlay" style="opacity: 0; position: absolute; top: 0px; left: 0px; width: 39px; height: 30px;">
	    <a title="" onclick="viewHTML(this, '#editor')" class="btn" data-original-title="View HTML"><i class="icon-code"></i></a>
	  </div>
	  <div class="btn-group">
	    <a title="" data-edit="undo" class="btn" data-original-title="Undo"><i class="icon-undo"></i></a>
	    <a title="" data-edit="redo" class="btn" data-original-title="Redo"><i class="icon-repeat"></i></a>
	  </div>
	</div>
	<div id="editor" class="editor" style="height:150px;"></div>
	<textarea id="notes" name="notes" style="display: none;"></textarea>
	<input type="button" onclick="validate()" class="btn" id="submitBtn" value="Create Note" />
	<input type="hidden" name="isEditing" id="isEditing" value="0" />
</form>

<script type="text/javascript">

	initToolbarBootstrapBindings();
	$('#editor').wysiwyg({
	  hotKeys: {
	  	'shift+tab': 'outdent',
	  	'tab' : 'indent',
	    'ctrl+b meta+b': 'bold',
	    'ctrl+i meta+i': 'italic',
	    'ctrl+u meta+u': 'underline',
	    'ctrl+z meta+z': 'undo',
	    'ctrl+y meta+y meta+shift+z': 'redo'
	  }
	});
	
	var datepicker = $.fn.datepicker.noConflict(); // return $.fn.datepicker to previously assigned value
	$.fn.bootstrapDP = datepicker;  // give $().bootstrapDP the bootstrap-datepicker functionality
	
	//Create a custom button for toggling HTML view on/off
	function viewHTML(btn, editor) {
		var $editor = $(editor);
		var viewMode = $editor.attr("data-viewMode");

		//Set default
		if(!viewMode) {
			viewMode = "text";
		}
		if(viewMode == "text") {
			var html = $(editor).html();
			$editor.text(html);
			$editor.attr("data-viewMode", "html");
			if(btn) $(btn).attr("data-original-title", "Switch to WYSIWYG Editor");
		} else {
			var text = $(editor).text();
			$editor.html(text);
			$editor.attr("data-viewMode", "text");
			if(btn) $(btn).attr("data-original-title", "View HTML");
		}
	}
	
	function copyHtmlContent(editor, textarea) {
		var $editor = $(editor);
		$editor.cleanHtml();
		var viewMode = $editor.attr("data-viewMode");
		if(viewMode == "html") {
			console.log($editor.text());
			$(textarea).val($editor.text());	
		} else {
			$(textarea).val($editor.html());
		}
	}
	
	function submitForm() {
		copyHtmlContent('#editor', '#notes');
		$('#addNotes').submit();
	}

	function validate() {
		var date = $('#date-input').val();	
		if(date) {
			$('#dateRequired').hide();
			submitForm();
		} else {
			$('#dateRequired').html("You must select a date.");
			$('#dateRequired').show();
			
		}
	}
	
	function checkForExistingNote(date) {
		if(date) {
			$.ajax({
				type: "POST",
				url: "/events/manage/php/post_notes.php",
				data: { "date-input" : date, mode : "check" },
				success: function(response){
			  		if(response) {
					  	$('#editor').html(response);	
					  	$('#submitBtn').val("Save Changes");
					  	$('#isEditing').val("1");
			  		}
			  		else {
				  		$('#editor').html("");	
				  		$('#submitBtn').val("Create Note");
				  		$('#isEditing').val("0");
			  		}
			  	}
			});			
		}
	}

	$(function() {
		$('#date-picker').bootstrapDP().on("changeDate", function() {
			$('#date-picker').bootstrapDP("hide");
			var date = $('#date-input').val();
			checkForExistingNote(date);
			$('#dateRequired').hide();
		});
	});
	
	if(window.location.search.indexOf("error") != -1) {
		$("#errordiv").show();
	}
	
	if(window.location.search.indexOf("success") != -1) {
		$("#successdiv").show();
	}

</script>
