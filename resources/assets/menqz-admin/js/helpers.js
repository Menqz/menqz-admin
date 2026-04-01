/*--------------------------------------------------*/
/* visual */
/*--------------------------------------------------*/

	var show = function (list,display) {
		if (typeof(display) === 'undefined'){
			display = "block";
		}
		if (!isNodeList(list)){
			var list = [list];
		}
		list.forEach(elm => {
			showElm(elm,display);
		});
	};
	function showElm(elm,display){
		if(elm.tagName == "TR"){
			elm.style.display = "table-row";
		}else{
			elm.style.display = display;
		}
	}

	var hide = function (list) {
		if (!isNodeList(list)){
			var list = [list];
			isNodeList(list)
		}
		list.forEach(elm => {
			elm.style.display = 'none';
		});
	};

	var toggle = function (list) {
		if (!isNodeList(list)){
			var list = [list];
		}
		list.forEach(elm => {
			let calculatedStyle = window.getComputedStyle(elm).display;
			if (calculatedStyle === 'block' || calculatedStyle === 'flex' || calculatedStyle === 'table-row') {
				elm.style.display = 'none';
				return;
			}
			showElm(elm);
		});
	};

/*--------------------------------------------------*/
/* lang function */
/*--------------------------------------------------*/

	var __ = function(trans_string){
		return admin_lang_arr[trans_string];
	}

	var trans = __;

/*--------------------------------------------------*/
/* array / object helpers */
/*--------------------------------------------------*/

	var merge_default = function(defaults,object, ...rest){
		return Object.assign({}, defaults, object, ...rest);
	}

	var arr_remove = function(arr,elem) {
		var indexElement = arr.findIndex(el => el == elem);
		if (indexElement != -1)
		  arr.splice(indexElement, 1);
		return arr;
	};

	var arr_includes = function(arr,elem) {
		var indexElement = arr.findIndex(el => el == elem);
		return (indexElement != -1)
	};

/*--------------------------------------------------*/
/* event Handlers  */
/*--------------------------------------------------*/

	function delegate(selector, handler) {

		return function(event) {
		  var targ = event.target;
		  do {
			if (targ.matches(selector)) {
			  handler.call(targ, event);
			}
		  } while ((targ = targ.parentNode) && targ != event.currentTarget);
		}
	  }

/*--------------------------------------------------*/
/* html elements */
/*--------------------------------------------------*/

	function getOuterHeigt(el) {
		// Get the DOM Node if you pass in a string
		el = (typeof el === 'string') ? document.querySelector(el) : el;

		var styles = window.getComputedStyle(el);
		var margin = parseFloat(styles['marginTop']) +
					 parseFloat(styles['marginBottom']);

		return Math.ceil(el.offsetHeight + margin);
	}

	function isNodeList(nodes) {
		var stringRepr = Object.prototype.toString.call(nodes);

		return typeof nodes === 'object' &&
			/^\[object (HTMLCollection|NodeList|Object)\]$/.test(stringRepr) &&
			(typeof nodes.length === 'number') &&
			(nodes.length === 0 || (typeof nodes[0] === "object" && nodes[0].nodeType > 0));
	}

	/**
	 * @param {String} HTML representing a single element
	 * @return {Element}
	 */
	function htmlToElement(html) {
		var template = document.createElement('template');
		html = html.trim(); // Never return a text node of whitespace as the result
		template.innerHTML = html;
		return template.content.firstChild;
	}

	/**
	 * @param {String} HTML representing any number of sibling elements
	 * @return {NodeList}
	 */
	function htmlToElements(html) {
		var template = document.createElement('template');
		template.innerHTML = html;
		return template.content.childNodes;
	}


/*--------------------------------------------------*/
/* Can Cascade Fields Functions */
/*--------------------------------------------------*/

function getValuesFrom(selector){
    var arr = []
    document.querySelectorAll(selector).forEach(el=>{
        arr.push(el.value);
    });
    return arr;
}

var inArray = function (find,arr){
    return arr.indexOf(find);
}
var operator_table = {
    '=': function(a, b) {
        if (Array.isArray(a) && Array.isArray(b)) {
            a.sort();
            b.sort();
            return a.join() == b.join()
        }
        return a == b;
    },
    '>': function(a, b) { return a > b; },
    '<': function(a, b) { return a < b; },
    '>=': function(a, b) { return a >= b; },
    '<=': function(a, b) { return a <= b; },
    '!=': function(a, b) {

        if (Array.isArray(a) && Array.isArray(b)) {
            a.sort();
            b.sort();
            return !(a.join() == b.join())
        }

            return a != b;
    },
    'in': function(a, b) { return inArray(a, b) != -1; },
    'notIn': function(a, b) { return inArray(a, b) == -1; },
    'has': function(a, b) { return inArray(b, a) != -1; },
    'oneIn': function(a, b) { return a.filter(v => b.includes(v)).length >= 1; },
    'oneNotIn': function(a, b) { return a.filter(v => b.includes(v)).length == 0; },
};

function waitForElement(selector, callback) {
    const el = document.querySelector(selector);

    if (el) {
        setTimeout(() => callback(el), 100);
        return;
    }

    const observer = new MutationObserver(() => {
        const el = document.querySelector(selector);
        if (el) {
            observer.disconnect();
            setTimeout(() => callback(el), 100);
        }
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
}

/**
 * Detecta se o usuário está em dispositivo móvel
 * @returns {boolean}
 */
function isMobile() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

/**
 * Executa os scripts encontrados no conteúdo HTML.
 * @param {HTMLElement} element - O elemento onde procurar scripts.
 */
function executeScripts (element) {
    const scripts = element.querySelectorAll('script');
    scripts.forEach((script) => {
        const newScript = document.createElement('script');
        newScript.type = script.type || 'text/javascript';
        if (script.src) {
            // Para scripts externos
            newScript.src = script.src;
            document.head.appendChild(newScript);
        } else {
            // Para scripts inline
            newScript.textContent = script.textContent;
            document.body.appendChild(newScript);
        }
    });
}
