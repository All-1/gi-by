(function() {

  'use strict';

  var stabs = function(options) {

    var el = document.querySelector(options.el);
    var tabNavigationLinks = el.querySelectorAll(options.tabNavigationLinks);
    var tabContentContainers = el.querySelectorAll(options.tabContentContainers);
    var activeIndex = 0;
    var initCalled = false;

    var init = function() {
      if (!initCalled) {
        initCalled = true;
        el.classList.remove('s-no-js');
        
        for (var i = 0; i < tabNavigationLinks.length; i++) {
          var link = tabNavigationLinks[i];
          handleClick(link, i);
        }
      }
    };

    var handleClick = function(link, index) {
      link.addEventListener('click', function(e) {
        e.preventDefault();
        goToTab(index);
      });
    };

    var goToTab = function(index) {
      if (index !== activeIndex && index >= 0 && index <= tabNavigationLinks.length) {
        tabNavigationLinks[activeIndex].classList.remove('s-is-active');
        tabNavigationLinks[index].classList.add('s-is-active');
        tabContentContainers[activeIndex].classList.remove('s-is-active');
        tabContentContainers[index].classList.add('s-is-active');
        activeIndex = index;
      }
    };

    return {
      init: init,
      goToTab: goToTab
    };

  };

  window.tabs = stabs;

})();

/*Собственно сам вызов табов сюда*/
var myTabs = tabs({
	el: '#stabs',
	tabNavigationLinks: '.s-tabs-nav__link',
	tabContentContainers: '.s-tab'
});
myTabs.init();