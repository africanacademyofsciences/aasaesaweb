var vinerhanditc = {
	src: '/flash/vinerhanditc.swf'
	,ratios: [6,1.41,9,1.35,15,1.29,21,1.25,22,1.22,27,1.24,29,1.21,34,1.22,41,1.21,45,1.2,46,1.21,59,1.2,68,1.19,69,1.2,96,1.19,97,1.18,102,1.19,103,1.18,112,1.19,114,1.18,116,1.19,120,1.18,121,1.19,1.18]
	};
	
	sIFR.delayCSS  = true;
	// sIFR.domains = ['novemberborn.net'] // Don't check for domains in this demo
	sIFR.activate(vinerhanditc);
	
	sIFR.replace(vinerhanditc, {
	selector: 'h2.special',
	css: [
	  '.sIFR-root {  background: transparent; color: #000000; text-align: left; font-weight: normal; font-size:16px; padding-left:10px; margin-left: 10px;}',
	  'a { background: transparent; text-decoration: none; color: #000000; }',
	  'a:link { color: #000000; }',
	  'a:hover { color: #333333; }'
	]
}
);