/*!
devtools-detect
https://github.com/sindresorhus/devtools-detect
By Sindre Sorhus
Fixed & improved by Ashraf Eshtawe
Version: 2.1
MIT License
*/
function devtools_isMobile()
{
	var isMobile = false; //initiate as false
	// device detection
	if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent) 
    || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0,4)))
	{ 
		isMobile = true;
	}
	return isMobile;
}

function isHighDensity(){
    return ((window.matchMedia && (window.matchMedia('only screen and (min-resolution: 124dpi), only screen and (min-resolution: 1.3dppx), only screen and (min-resolution: 48.8dpcm)').matches || window.matchMedia('only screen and (-webkit-min-device-pixel-ratio: 1.3), only screen and (-o-min-device-pixel-ratio: 2.6/2), only screen and (min--moz-device-pixel-ratio: 1.3), only screen and (min-device-pixel-ratio: 1.3)').matches)) || (window.devicePixelRatio && window.devicePixelRatio > 1.3));
}


function isRetina(){
    return ((window.matchMedia && (window.matchMedia('only screen and (min-resolution: 192dpi), only screen and (min-resolution: 2dppx), only screen and (min-resolution: 75.6dpcm)').matches || window.matchMedia('only screen and (-webkit-min-device-pixel-ratio: 2), only screen and (-o-min-device-pixel-ratio: 2/1), only screen and (min--moz-device-pixel-ratio: 2), only screen and (min-device-pixel-ratio: 2)').matches)) || (window.devicePixelRatio && window.devicePixelRatio >= 2)) && /(iPad|iPhone|iPod)/g.test(navigator.userAgent);
}

const devtools = {
	isOpen: false,
	orientation: undefined,
};

const threshold = 160;

const emitEvent = (isOpen, orientation) => {
	globalThis.dispatchEvent(new globalThis.CustomEvent('devtoolschange', {
		detail: {
			isOpen,
			orientation,
		},
	}));
};

const main = ({emitEvents = true} = {}) => {
	
	let zoom_devicePixelRatio = window.devicePixelRatio;
	
	//need some work here
	//if isRetina() zoom_devicePixelRatio = zoom_devicePixelRatio /2;
	
	//console.log("isHighDensity() " + isHighDensity());
	//console.log("isRetina() " + isRetina());
	
	if(devtools_isMobile() && localStorage.getItem("wccp_was_desktop_with_div_tools") != "yes") return; // exit if on mobile only
	var dev_tools_mobile_on_desktop_is_open = false;
	if(devtools_isMobile() && localStorage.getItem("wccp_was_desktop_with_div_tools") == "yes") dev_tools_mobile_on_desktop_is_open = true; // exit if on mobile only
	const widthThreshold = (globalThis.outerWidth - (globalThis.innerWidth * zoom_devicePixelRatio)) > threshold;
	//console.log("widthThreshold_value " + (globalThis.outerWidth - (globalThis.innerWidth * zoom_devicePixelRatio)))
	const heightThreshold = (globalThis.outerHeight - (globalThis.innerHeight * zoom_devicePixelRatio)) > threshold;
	//console.log("heightThreshold_value " + (globalThis.outerHeight - (globalThis.innerHeight * zoom_devicePixelRatio)))
	const orientation = widthThreshold ? 'vertical' : 'horizontal';
	const orientation2 = globalThis.orientation || globalThis.screen.orientation["angle"];
	var orientation_chrome = false;
	if(typeof globalThis.orientation != "undefined") orientation_chrome = true;
	var orientation_edge = false;
	if(navigator.userAgent.toLowerCase().indexOf('edg') > -1)
	{
		if(typeof globalThis.orientation != "undefined") orientation_edge = true;
	}
	
	//console.log("inner " + globalThis.innerWidth + "outer " + globalThis.outerWidth + "thershold" + ((globalThis.outerWidth - globalThis.innerWidth)));
	
	var dev_tools_firefox_mobile_is_open = false;
	
	if(navigator.userAgent.toLowerCase().indexOf('firefox') > -1)
	{
		if(globalThis.outerWidth - globalThis.innerWidth == 0) dev_tools_firefox_mobile_is_open = true;
	}
	
	//console.log("0-(heightThreshold && widthThreshold) " + heightThreshold + " " + widthThreshold);
	
	if(!(heightThreshold && widthThreshold) && ((globalThis.Firebug && globalThis.Firebug.chrome && globalThis.Firebug.chrome.isInitialized)
		|| widthThreshold || heightThreshold || dev_tools_mobile_on_desktop_is_open || dev_tools_firefox_mobile_is_open))
	{
		if ((!devtools.isOpen || devtools.orientation !== orientation) && emitEvents) {
			emitEvent(true, orientation);
			//console.log("1-orientation " + orientation);
		}

		devtools.isOpen = true;
		devtools.orientation = orientation;
		//console.log("2-orientation " + devtools.orientation);
	}else
	{
		if(devtools.isOpen && emitEvents) {
			emitEvent(false, undefined);
			//console.log("3-orientation " + undefined);
		}

		devtools.isOpen = false;
		devtools.orientation = undefined;
		//console.log("4-orientation " + undefined);
	}
};

main({emitEvents: false});
setInterval(main, 500);

export default devtools;
