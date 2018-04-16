function settings() {
    var rows = document.getElementsByTagName('tr');
    var tables = document.getElementsByTagName('table');
    var tablebodies = document.getElementsByTagName('tbody');

    if (rows.length == 0 || tables.length == 0 || tablebodies.length == 0) {
      console.log(rows);

      return false;
    } else {
      console.log(rows);

      document.getElementsByTagName('tbody')[0].setAttribute('ondrop', 'drop(event)');
      document.getElementsByTagName('tbody')[0].setAttribute('ondragover', 'allowDrop(event)');

      for (var i = 0; i < tables.length; i++) {
        tables[i].getAttribute('id') == undefined ? tables[i].setAttribute('id', 'table_' + i) :'';
      }
      for (var i = 0; i < rows.length; i++) {

        rows[i].getAttribute('id') == undefined ? rows[i].setAttribute('id', 'row_' + i) : '';
        rows[i].classList.add('draggable');
        rows[i].classList.add('drag-handler');
        rows[i].setAttribute('draggable', true);
        rows[i].setAttribute('ondragstart', "drag(event)");
        rows[i].setAttribute('ondrop', "drop(event)");

      }
    }
  }

  settings();

  function allowDrop(ev) {
    ev.preventDefault();
  }

  function drag(ev) {
    ev.dataTransfer.setData("row", ev.target.id);
  }

  function drop(ev) {

    ev.preventDefault();
    var data = ev.dataTransfer.getData("row");
    var target = ev.target.parentNode;

    var draggedElement = document.getElementById(data);
    target.parentNode.insertBefore(draggedElement, target);

    console.log('target');
    console.log(target);
    console.log("draggedElement");
    console.log(draggedElement);

  }
