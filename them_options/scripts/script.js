var ViewModel = function() {
  var self = this;

  self.userList1 = ko.observableArray([]);

  self.userList2 = ko.observableArray([]);
  
  self.actingOnThings = ko.observable(false);
  
  self.canSort = ko.observable(true);

  for (var i = 0; i < 1000; i++) {
    self.userList2.push({
      Name: 'SP' + i,
      Id: i,
      Selected: ko.observable(false)
    })
  }

  self.multiSortableOptions = {
    revert: 100,
    tolerance: "pointer",
    distance: 15,
    stop: function() {
      if(self.$selected) {
        self.$selected.fadeIn(100);
      }
    },
    helper: function(event, $item) {
      // probably a better way to pass these around than in id attributes, but it works
      var dbId = $item.parent().attr('id').split('_')[1],
        itemId = $item.attr('id').split('_')[1],
        db = myViewModel['userList' + dbId];

      // If you grab an unhighlighted item to drag, then deselect (unhighlight) everything else
      if (!$item.hasClass('selected')) {
        ko.utils.arrayForEach(db(), function(item) {
          //needs to be like this for string coercion
          item.Selected(item.Id == itemId);
        });
      }

      // Create a helper object with all currently selected items
      var $selected = $item.parent().find('.selected');
      var $helper;
      if ($selected.size() > 1) {
        $helper = $('<li class="item selected">You have ' + $selected.size() + ' items selected.</li>');
        $selected.fadeOut(100);
        $selected.removeClass('selected');
      } else {
        $helper = $selected;
      }
      self.$selected = $selected;

      return $helper;
    }
  };

  var moveTheseTo = function(items, from, to, atPosition) {
    self.actingOnThings(true);
    
    var copyFunction = function() {
      var newArgs = [atPosition, 0].concat(items);
  
      ko.utils.arrayForEach(to(), function(item) {
        item.Selected(false);
      });
  
      ko.utils.arrayForEach(items, function(item) {
        from.remove(item);
      });
      
      to.splice.apply(to, newArgs);
      self.actingOnThings(false);
    }
    
    
    if(items.length > 300) {
      setTimeout(copyFunction, 100);
    } else {
      copyFunction();
    }
  };
  
  self.selectAllItemsIn = function(list) {
    ko.utils.arrayForEach(list(), function(item) {
      item.Selected(true);
    });
  };
  
  self.moveAllFunction = function(from, to) {
    return function() {
      var items = ko.utils.arrayFilter(from(), function(item) {
        return item.Selected();
      });
      moveTheseTo(items, from, to, to().length);
    };
  };

  self.beforeMove = function(args, event, ui) {
    if(
      args.sourceParent === args.targetParent 
      && args.targetPosition === args.sourcePosition
    ) {
      self.$selected.fadeIn(100);
      return;
    }
    
    event.cancelDrop = true;
  };

  self.afterMove = function(args, event, ui) {
    
    var items = ko.utils.arrayFilter(args.sourceParent(), function(item) {
      return item.Selected();
    });
    
    moveTheseTo(items, args.sourceParent, args.targetParent, args.targetIndex);
    
    args.item.Selected(true);

  };

  self.selectProcedure = function(array, $data, event) {
    if (!event.ctrlKey && !event.metaKey && !event.shiftKey) {
      $data.Selected(true);
      ko.utils.arrayForEach(array, function(item) {
        if (item !== $data) {
          item.Selected(false);
        }
      });
    } else if (event.shiftKey && !event.ctrlKey && self._lastSelectedIndex > -1) {
      var myIndex = array.indexOf($data);
      if (myIndex > self._lastSelectedIndex) {
        for (var i = self._lastSelectedIndex; i <= myIndex; i++) {
          array[i].Selected(true);
        }
      } else if (myIndex < self._lastSelectedIndex) {
        for (var i = myIndex; i <= self._lastSelectedIndex; i++) {
          array[i].Selected(true);
        }
      }

    } else if (event.ctrlKey && !event.shiftKey) {
      $data.Selected(!$data.Selected());
    }
    self._lastSelectedIndex = array.indexOf($data);
  };

};

myViewModel = new ViewModel();

ko.applyBindings(myViewModel);