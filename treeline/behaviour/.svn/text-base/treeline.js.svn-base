function updatePanels(name,t) {
		// name is the name of the panels block [usually "panels"]
		// t is the object containing the select name and value

		// This function needs to:
		// Read in the current value of treeline_[name]
		var currentPanelList = document.getElementById('treeline_'+name).value;
		// Read in the current value of t.name and extract treeline_[name]_(.+)
		var currentPanel = (t.name).replace("treeline_" + name + "_","");
		// Read in the current value of t.value
		// If we're trying to delete a panel, remove that panel from the list:
		if (t.value == 'DELETE') {
			var newPanelList = currentPanelList.replace(currentPanel, '');
			// That might leave us with commas in the wrong place, so we need to strip them out
			// We could use arrays to get around this, but that might be overkill
			// There may also be a better RegEx for this, but JavaScript RegEx support is fairly weak:
			newPanelList = newPanelList.replace(/^,/,'');
			newPanelList = newPanelList.replace(/,$/,'');
			newPanelList = newPanelList.replace(/,,/,',');					
		}
		else {
			// Otherwise, replace \1 in treeline_[name] with t.value
			var newPanelList = currentPanelList.replace(currentPanel, t.value);
		}
		
		// Update to this: instead of using querystrings, we need to update a hidden field ['treeline_'+name?] and submit the page
		document.getElementById('treeline_'+name).value = newPanelList;
		// alert("debug: " + name + ',' + document.getElementById('treeline_'+name).value +',' + newPanelList);
		document.forms[0].submit();
		
		/*
		
		// Refresh the page with the new value as ?treeline_[name]=xxx
		var url = String(document.location);
		// If we've already set this panelList in the URL, update it
		if (url.indexOf('treeline_'+name+'='+currentPanelList) > -1) {
			url = url.replace('treeline_'+name+'='+currentPanelList, 'treeline_'+name+'='+newPanelList);
		}
		// If not, append the querystring:
		else {
			url = (document.location + '&treeline_'+name+'='+newPanelList);
		}
		document.location = url;
		
		*/
}

function addPanel(name,t) {
		// name is the name of the panels block [usually "panels"]
		// t is the object containing the select name and value

		// This function needs to:
		// Read in the current value of treeline_[name]
		var currentPanelList = document.getElementById('treeline_'+name).value;
		// Append the new panel to that list
		var newPanelList = currentPanelList + ',' + t.value;

		// Update to this: instead of using querystrings, we need to update a hidden field ['treeline_'+name?] and submit the page
		document.getElementById('treeline_'+name).value = newPanelList;
		document.forms[0].target='_self';
		document.forms[0].submit();

		/*

		// Refresh the page with the new paneList
		var url = String(document.location);
		// If we've already set this panelList in the URL, update it
		if (url.indexOf('treeline_'+name+'='+currentPanelList) > -1) {
			url = url.replace('treeline_'+name+'='+currentPanelList, 'treeline_'+name+'='+newPanelList);
		}
		// If not, append the querystring:
		else {
			url = (document.location + '&treeline_'+name+'='+newPanelList);
		}
		document.location = url;
		
		*/
}