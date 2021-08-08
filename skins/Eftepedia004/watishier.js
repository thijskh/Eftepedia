(function() {
  function showDialog() {
    var dialog = document.createElement('div');
    var returnObject = {
      visible: true,
      element: dialog,
    };
    dialog.className = 'dialog';
    dialog.addEventListener('click', function(){
      event.stopImmediatePropagation();
      event.preventDefault();
      dialog.parentElement.removeChild(dialog);
      returnObject.visible = false;
      if (returnObject.onClose) returnObject.onClose();
    });
    
    document.body.appendChild(dialog);
    return returnObject;
  }
  
  function ajax(url, success, error) {
    xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
      if (xmlhttp.readyState == XMLHttpRequest.DONE ) {
        if(xmlhttp.status == 200){
          if (success) success(xmlhttp.responseText);
        }
        else {
          if (error) error(xmlhttp.status, xmlhttp.responseText)
        }
      }
    }
    xmlhttp.open("GET", url, true);
    xmlhttp.send();
  }

  window.addEventListener('DOMContentLoaded', function(){
    if (navigator.geolocation) {
      document.body.classList.add('geosearch');
      var hier = document.getElementById('watishier');
      if (!hier) return;
      var menuitem = document.createElement('a');
      hier.parentNode.insertBefore(menuitem, hier.nextSibling);
      menuitem.href = '';
      menuitem.innerText = 'Hier';  
      menuitem.title = 'Zoek in je omgeving';
      menuitem.addEventListener('click', function(event){
        event.stopImmediatePropagation();
        event.preventDefault();
        
        var dialog = showDialog();
        dialog.element.classList.add('geosearch');
        
        navigator.geolocation.getCurrentPosition(function(position){
          var coords = position.coords.latitude.toString() + '|' + position.coords.longitude.toString();
          //coords = '51.647285|5.046115'; // = FM Plein
          var url = '/lemma/api.php?format=json&action=query&list=geosearch&gsradius=100000&gscoord=' + coords + '&gsprimary=primary&gsprop=type|name';
          ajax(url, 
            function(data){
              var geodata = JSON.parse(data);
              var results = geodata.query.geosearch;
              
              var list = document.createElement('ul');
              list.className = 'geosearch';
              dialog.onClose = function() {
                list.parentElement.removeChild(list);
              }
              if (!dialog.visible) return;
              menuitem.appendChild(list);
              for (var i = 0; i < results.length; i++) {
                var item = document.createElement('li');
                list.appendChild(item);
                item.className = results[i].type;

                var link = document.createElement('a');
                item.appendChild(link);
                link.href = '/lemma/' + results[i].title.replace(' ', '_');
                link.innerText = results[i].title;
                link.addEventListener('click', function(event){location.pathname = link.href; event.stopImmediatePropagation();});
              }
              
            }, 
            function(code, data){
              alert(code + ', ' + data);
            }
          );
        });
      });
    }
  });

})();