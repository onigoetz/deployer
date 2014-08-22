
/*
 Copyright (C) 2021 Pascal de Vink (Tweakers.net)

 This library is free software; you can redistribute it and/or
 modify it under the terms of the GNU Lesser General Public
 License as published by the Free Software Foundation; either
 version 2.1 of the License, or (at your option) any later version.

 This library is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 Lesser General Public License for more details.

 You should have received a copy of the GNU Lesser General Public
 License along with this library; if not, write to the Free Software
 Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA
 */
var ScrollSpy = (function()
{
    var elements = {};

    if (document.addEventListener)
    {
        document.addEventListener("touchmove", handleScroll, false);
        document.addEventListener("scroll", handleScroll, false);
    }
    else if (window.attachEvent)
    {
        window.attachEvent("onscroll", handleScroll);
    }


    function spyOn(domElement)
    {
        var element = {};
        element['domElement'] = domElement;
        element['isInViewPort'] = null;
        elements[domElement.id] = element;
    }

    function isVisible(element, start, end) {
        var elementPosition = getPositionOfElement(element.domElement);

        return elementPosition >= start && elementPosition <= end;
    }

    function handleScroll()
    {
        var viewportStart = document.documentElement.scrollTop ? document.documentElement.scrollTop: document.body.scrollTop,
            viewportEnd = viewportStart + Math.max(document.documentElement.clientHeight, window.innerHeight || 0);


        for (var i in elements) {

            var element =  elements[i],
                visible = isVisible(element, viewportStart, viewportEnd);

            if (element.isInViewPort != false && !visible) {
                fireEvent('ScrollSpyOutOfSight', element.domElement);
                element.isInViewPort = false;
            }

            if (element.isInViewPort != true && visible) {
                fireEvent('ScrollSpyBackInSight', element.domElement);
                element.isInViewPort = true;
            }
        }
    }

    function fireEvent(eventName, domElement)
    {
        var event;
        if (document.createEvent)
        {
            event = document.createEvent('HTMLEvents');
            event.initEvent(eventName, true, true);
            event.data = domElement;
            document.dispatchEvent(event);
        }
        else if (document.createEventObject)
        {
            event = document.createEventObject();
            event.data = domElement;
            event.expando = eventName;
            document.fireEvent('onpropertychange', event);
        }
    }

    function getPositionOfElement(domElement)
    {
        var pos = 0;
        while (domElement != null)
        {
            pos += domElement.offsetTop;
            domElement = domElement.offsetParent;
        }
        return pos;
    }

    return {
        spyOn: spyOn,
        firstScroll: handleScroll
    };
})();


var nav = document.getElementById('nav'),
    nav_inner = nav.childNodes[0];

/**
 * Handles the page being scrolled by ensuring the
 * navigation is always in view.
 */
function handleScroll(){
    // determine the distance scrolled down the page
    var offset = window.pageYOffset ? window.pageYOffset : document.documentElement.scrollTop,
        position = nav.offsetTop -30;

    toggleClass(nav, 'fixed', offset > position);

    if (offset > position) {
        nav_inner.style.top =  (offset - position) + "px";
    }
}

function toggleClass(element, className, isActive) {
    if (isActive) {
        if(element.className == '') { element.className = className; }
    } else {
        if(element.className != '') { element.className = ''; }
    }
}



// check that this is a relatively modern browser
if (window.XMLHttpRequest) {
    // add the scroll event listener
    if (window.addEventListener) {
        window.addEventListener('scroll', handleScroll, false);
    } else {
        window.attachEvent('onscroll', handleScroll);
    }
    handleScroll();
}

function toc_visible(event) {
    toggleClass(document.getElementById("nav-" + event.data.id), 'active', true);
}

function toc_hidden(event) {
    toggleClass(document.getElementById("nav-" + event.data.id), 'active', false);
}

var node;
for (var i = 0; i < generated_toc.headings.length; i++) {
    node = generated_toc.headings[i];
    ScrollSpy.spyOn(node);

    if (document.addEventListener)
    {
        document.addEventListener("ScrollSpyOutOfSight", toc_hidden, false);
        document.addEventListener("ScrollSpyBackInSight", toc_visible, false);
    }
    else if (window.attachEvent)
    {
        window.attachEvent("ScrollSpyOutOfSight", toc_hidden);
        window.attachEvent("ScrollSpyBackInSight", toc_visible);
    }
}

ScrollSpy.firstScroll();
