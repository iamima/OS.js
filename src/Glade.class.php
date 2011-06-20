<?php
/*!
 * @file
 * Contains Glade Class
 * @author Anders Evenrud <andersevenrud@gmail.com>
 * @license GPLv3 (see http://www.gnu.org/licenses/gpl-3.0.txt)
 * @created 2011-06-16
 */

function get_inner_html( $node ) { 
    $innerHTML= ''; 
    $children = $node->childNodes; 
    foreach ($children as $child) { 
        $innerHTML .= $child->ownerDocument->saveXML( $child ); 
    } 

    return $innerHTML; 
} 

class Glade
{
  private $_sClassName = "";
  private $_aWindows = Array();

  protected static $Counter = 0;

  protected static $ShortTags = Array(
    "GtkImage", "GtkEntry"
  );

  public static $ClassMap = Array(
    "GtkScale" => Array(
      "element" => "div"
    ),
    "GtkCellRendererText" => Array(
      "element" => "option"
    ),
    "GtkIconView" => Array(
      "element" => "div",
      "gobject" => true
    ),
    "GtkLabel" => Array(
      "element" => "div",
      "gobject" => true
    ),
    "GtkColorButton" => Array(
      "element" => "div",
      "gobject" => true
    ),

    "GtkDrawingArea" => Array(
      "element" => "canvas",
      "classes" => Array("Canvas")
    ),

    "GtkSeparator" => Array(
      "element" => "div",
      "inner"   => "hr"
    ),

    "GtkBox" => Array(
      "element" => "table"
    ),
    "GtkButtonBox" => Array(
      "element" => "table"
    ),

    "GtkCheckButton" => Array(
      "element" => "input",
      "type"    => "checkbox",
      "wrapped" => true
      //"gobject" => true
    ),

    "GtkComboBox" => Array(
      "element" => "select",
      "gobject" => true
    ),

    "GtkToolItemGroup" => Array(
      "element" => "button"
    ),
    "GtkButton" => Array(
      "element" => "button",
      "gobject" => true
    ),
    "GtkTextView" => Array(
      "element" => "textarea",
      "gobject" => true
    ),
    "GtkImage" => Array(
      "element" => "img",
      "src"     => "/img/blank.gif",
      "gobject" => true
    ),
    "GtkEntry" => Array(
      "element" => "input",
      "type"    => "text",
      "gobject" => true
    ),
    "GtkMenuBar" => Array(
      "element" => "ul"
    ),
    "GtkMenuItem" => Array(
      "element" => "li"
    ),
    "GtkMenu" => Array(
      "element" => "ul"
    ),
    "GtkImageMenuItem" => Array(
      "element" => "li"
    ),
    "GtkRadioMenuItem" => Array(
      "element" => "li"
    ),
    "GtkToolbar" => Array(
      "element" => "ul"
    ),
    "GtkToolItem" => Array(
      "element" => "li"
    ),
    "GtkToggleToolButton" => Array(
      "element" => "li",
      "inner"   => "button"
    ),

    "GtkNotebook" => Array(
      "element" => "div",
      "inner"   => "ul"
    )
  );

  protected static $Stock = Array(
    "gtk-cancel" => Array(
      "label" => "Cancel",
      "icon" => "actions/gtk-cancel.png"
    ),
    "gtk-new" => Array(
      "label" => "New",
      "icon" => "actions/gtk-new.png"
    ),
    "gtk-close" => Array(
      "label" => "Close",
      "icon" => "actions/gtk-close.png"
    ),
    "gtk-home" => Array(
      "label" => "Home",
      "icon" => "actions/gtk-home.png"
    ),
    "gtk-refresh" => Array(
      "label" => "Refresh",
      "icon" => "actions/gtk-refresh.png"
    ),
    "gtk-open" => Array(
      "label" => "Open",
      "icon" => "actions/gtk-open.png"
    ),
    "gtk-save" => Array(
      "label" => "Save",
      "icon" => "actions/gtk-save.png"
    ),
    "gtk-save-as" => Array(
      "label" => "Save as...",
      "icon" => "actions/gtk-save-as.png"
    ),
    "gtk-cut" => Array(
      "label" => "Cut",
      "icon" => "actions/gtk-cut.png"
    ),
    "gtk-copy" => Array(
      "label" => "Copy",
      "icon" => "actions/gtk-copy.png"
    ),
    "gtk-paste" => Array(
      "label" => "Paste",
      "icon" => "actions/gtk-paste.png"
    ),
    "gtk-delete" => Array(
      "label" => "Delete",
      "icon" => "actions/gtk-delete.png"
    ),
    "gtk-about" => Array(
      "label" => "About",
      "icon" => "actions/gtk-about.png"
    ),
    "gtk-quit" => Array(
      "label" => "Quit",
      "icon" => "actions/gtk-quit.png"
    )
  );

  public function __construct($filename, $xml_data) {
    $cn = str_replace(".glade", "", basename($filename)); // FIXME
    $this->_sClassName = $cn;

    foreach ( $xml_data->object as $root ) {
      $class = (string) $root['class'];
      $id    = (string) $root['id'];

      // Properties
      $properties = Array(
        "type"            => $class == "GtkWindow" ? "window" : "dialog",
        "title"           => "",
        "icon"            => "",
        "is_draggable"    => true,
        "is_resizable"    => true,
        "is_scrollable"   => false,
        "is_sessionable"  => true,
        "is_minimizable"  => true,
        "is_maximizable"  => true,
        "is_closable"     => true,
        "is_orphan"       => false,
        "width"           => 500,
        "height"          => 300,
        "gravity"         => ""
      );

      foreach ( $root->property as $p ) {
        $pv = (string) $p;
        switch ( (string) $p['name'] ) {
          case 'resizable' :
            if ( $pv == "False" ) {
              $properties['is_resizable'] = false;
            }
            break;
          case 'title' :
            if ( $pv ) {
              $properties['title'] = $pv;
            }
          break;
          case 'icon' :
            if ( $pv ) {
              $properties['icon'] = $pv;
            }
          break;
          case 'default_width' :
            $properties['width'] = (int) $pv;
          break;
          case 'default_height' :
            $properties['height'] = (int) $pv;
          break;
          case 'window_position' :
            $properties['gravity'] = $pv;
          break;
        }
      }

      $classes = Array($class, $cn, $id);

      // Window HTML document
      $x = new DOMImplementation();
      $doc = $x->createDocument();
      $doc->xmlVersion    = "1.0";
      $doc->formatOutput  = true;
      $doc->encoding      = 'UTF-8';

      $n_window = $doc->createElement("div");
      $n_window->setAttribute("class", $id);

      $n_content = $doc->createElement("div");
      $n_content->setAttribute("class", implode(" ", $classes));

      $n_window->appendChild($n_content);

      // Parse Glade document childs
      $signals = $this->_parseChild($doc, $n_content, $root, $root);

      $doc->appendChild($n_window);

      // Append window to registry
      $html = str_replace("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n", "", $doc->saveXML());
      $this->_aWindows[$id] = Array(
        "properties" => $properties,
        "signals"    => $signals,
        "content"    => $html
      );

    }
  }

  protected function _fill($doc, $node, $short = false) {
    if ( !$node->hasChildNodes() && !$short ) {
      $node->appendChild($doc->createTextNode(''));
    }
  }

  protected final function _parseChild($doc, $doc_node, $gl_root, $gl_node, Array &$signals = Array()) {
    if  ( isset($gl_node->child) ) {
      if ( $children = $gl_node->child ) {
        foreach  ( $children as $c ) {
          $class    = (string) $c->object['class'];
          $id       = (string) $c->object['id'];

          $elid     = "";
          $classes  = Array($class);
          $styles   = Array();
          $attribs  = Array();

          $oclasses = Array();
          $ostyles  = Array();

          $inner = null;
          $outer = null;
          $node_type = "div";

          $append_root = $doc_node;

          // Apply built-in attributes
          $advanced_ui = false;
          $tabbed_ui = false;

          if ( isset(self::$ClassMap[$class]) ) {
            $node_type = self::$ClassMap[$class]["element"];

            if ( isset(self::$ClassMap[$class]["gobject"]) ) {
              if ( self::$ClassMap[$class]["gobject"] ) {
                $classes[] = "GtkObject";
              }
            }
            if ( isset(self::$ClassMap[$class]["classes"]) ) {
              $classes = array_merge($classes, self::$ClassMap[$class]["classes"]);
            }

            if ( isset(self::$ClassMap[$class]["type"]) ) {
              $attribs["type"] = self::$ClassMap[$class]["type"];
            }

            if ( isset(self::$ClassMap[$class]["value"]) ) {
              $attribs["value"] = self::$ClassMap[$class]["value"];
            }

            if ( isset(self::$ClassMap[$class]["inner"]) ) {
              $inner = $doc->createElement(self::$ClassMap[$class]["inner"]);
            }

            if ( isset(self::$ClassMap[$class]["wrapped"]) ) {
              $outer = $doc->createElement("div");
            }
          }

          // Create HTML node
          $node = $doc->createElement($node_type);

          if ( $class == "GtkCheckButton" ) {
            //$chk = $doc->createElement("input");
            //$chk->setAttribute("type", "checkbox");
            $label = $doc->createElement("label");

            //$node->appendChild($chk);
            $node->appendChild($label);
            $inner = $label;
          } else if ( $class == "GtkCellRendererText" ) {
            $ex = explode("_", $id, 2);
            $node->setAttribute("value", end($ex));
            $node->appendChild(new DomText(end($ex)));
          } else if ( $class == "GtkNotebook" ) {
            // FIXME
          } else {
            if ( isset($c['type']) && ((string)$c['type'] == "tab") ) {
              $append_root = $doc_node->getElementsByTagName("ul")->item(0); //->appendChild($li);
              $outer = $doc->createElement("li");
              $advanced_ui = true;
              $tabbed_ui = "tab-" . (self::$Counter++);
            } else {
              if ( $gl_node['class'] == "GtkNotebook" ) {
                $advanced_ui = true;
                $elid = "tab-" . (self::$Counter);
                $classes[] = "GtkTab";
              }
            }
          }

          // Parse Glade element signals
          if ( isset($c->object->signal) ) {
            foreach ( $c->object->signal as $p ) {
              $pk = (string) $p['name'];
              $pv = (string) $p['handler'];

              switch ( $pk ) {
                case "item-activated" :
                case "group-changed" :
                case "select" :
                case "activate" :
                case "clicked" :
                  $pk = "click";
                  break;
              }

              if ( !isset($signals[$id]) ) {
                $signals[$id] = Array();
              }
              $signals[$id][$pk] = $pv;
            }
          }

          // Parse Glade element packing
          $packed = false;
          if ( !$advanced_ui ) {
            if ( isset($c->packing) ) {
              foreach ( $c->packing->property as $p ) {
                $pk = (string) $p['name'];
                $pv = (string) $p;

                switch ( $pk ) {
                  case "expand" :
                    if ( $pv == "True" ) {
                      $oclasses[] = "Expand";
                    }
                    break;

                  case "fill" :
                    if ( $pv == "True" ) {
                      $oclasses[] = "Fill";
                    }
                    break;

                  case "position" :
                    $packed = true;
                    $oclasses[] = "GtkBoxPosition";
                    $oclasses[] = "Position_{$pv}";
                    break;

                  case "x" :
                    $styles[] = "left:{$pv}px";
                    break;

                  case "y" :
                    $styles[] = "top:{$pv}px";
                    break;
                }
              }
            }
          }

          // Parse Glade element attributes
          $orient = "";
          if ( isset($c->object->property) ) {
            foreach ( $c->object->property as $p ) {
              $pk = (string) $p['name'];
              $pv = (string) $p;

              switch ( $pk ) {
                case "visible" :
                  if ( $pv == "False" ) {
                    $classes[] = "Hidden";
                  }
                  break;

                case "width_request" :
                  $styles[] = "width:{$pv}px";
                  break;

                case "height_request" :
                  $styles[] = "height:{$pv}px";
                  break;

                case "active" :
                  if ( $pv == "True" ) {
                    if ( $node_type == "input" ) {
                      $node->setAttribute("checked", "checked");
                    } else {
                      $classes[] = "Checked";
                    }
                  }
                  break;

                case "orientation" :
                  $orient = ucfirst($pv);
                  break;

                case "label" :
                  $icon    = null;
                  $tooltip = null;
                  $orig    = $pv;

                  if ( ($stock = $this->_getStockImage($orig)) !== null ) {
                    list($pv, $icon, $tooltop) = $stock;
                  }

                  if ( $icon ) {
                    $img = $doc->createElement("img");
                    $img->setAttribute("alt", $orig);
                    $img->setAttribute("src", $icon);
                    $node->appendChild($img);
                  }

                  if ( $tooltip ) {
                    $node->setAttribute("title", $tooltip);
                  }

                  if ( $class == "GtkButton" || $class == "GtkLabel" ) {
                    if ( $class == "GtkLabel" && $tabbed_ui ) {
                      $link = $doc->createElement("a");
                      $link->appendChild(new DomText($pv));
                      $link->setAttribute("href", "#{$tabbed_ui}");
                      $node->appendChild($link);
                    } else {
                      $node->appendChild(new DomText($pv));
                    }
                  } else if ( $class == "GtkToggleToolButton" || $class == "GtkCheckButton" ) {
                    $inner->appendChild(new DomText($pv));
                  } else if ( $class == "GtkMenuItem" || $class == "GtkImageMenuItem" || $class == "GtkRadioMenuItem" ) {
                    $node->appendChild(self::_getHotkeyed($doc, $pv));
                  }
                  break;
              }
            }
          }

          if ( $orient )  {
            $classes[] = $orient;
          } else {
            if ( $class == "GtkBox" || $class == "GtkButtonBox" ) {
              $classes[] = "Horizontal";
            }
          }

          $classes[] = $id; // Append element ID lastly

          // Apply information gathered
          if ( $classes ) {
            if ( $outer ) {
              $outer->setAttribute("class", implode(" ", $classes));
            } else {
              $node->setAttribute("class", implode(" ", $classes));
            }
          }

          if ( $inner !== null ) {
            $node->appendChild($inner);
          }

          foreach ( $attribs as $ak => $av ) {
            $node->setAttribute($ak, $av);
          }

          if ( $packed ) {

            if ( !$orient ) {
              $orient = "Horizontal";
              if ( $gl_node['class'] == "GtkBox" ) {
                foreach ( $gl_node->property as $p ) {
                  if ( (string) $p['name'] == "orientation" ) {
                    if ( $d = (string) $p ) {
                      $orient = ucfirst($d);
                    }
                    break;
                  }
                }
              }
            }

            if ( $orient != "Vertical" ) {
              if ( !($temp = $doc_node->getElementsByTagName("tr")->item(0)) ) {
                $temp = $doc->createElement("tr");
                $append_root->appendChild($temp);
              }
            } else {
              $temp = $doc->createElement("tr");
              $append_root->appendChild($temp);
            }


            $temp2 = $doc->createElement("td");
            $temp2->setAttribute("class", implode(" ", $oclasses));
            if ( $styles ) {
              $temp2->setAttribute("style", implode(";", $styles));
            }
            if ( $elid ) {
              $temp2->setAttribute("id", $elid);
            }
            if ( $outer ) {
              $outer->appendChild($node);
              $temp2->appendChild($outer);
            } else {
              $temp2->appendChild($node);
            }
            $temp->appendChild($temp2);
          } else {
            if ( $styles ) {
              $node->setAttribute("style", implode(";", $styles));
            }
            if ( $elid ) {
              $node->setAttribute("id", $elid);
            }

            if ( $outer ) {
              $outer->appendChild($node);
              $append_root->appendChild($outer);
            } else {
              $append_root->appendChild($node);
            }
          }

          $this->_parseChild($doc, $node, $gl_node, $c->object, $signals);

          if ( !in_array($class, self::$ShortTags) ) {
            $this->_fill($doc, $node);
          }
        }
      }
    }

    return $signals;
  }

  protected final static function _getStockImage($stock, $size = "16x16") {
    if ( isset(self::$Stock[$stock]) ) {
      $label   = self::$Stock[$stock]['label'];
      $icon    = self::$Stock[$stock]['icon'];
      $tooltip = null;
      if ( isset(self::$Stock[$stock]['tooltip']) ) {
        $tooltip = self::$Stock[$stock]['tooltip'];
      }

      $path = sprintf("/img/icons/%s/%s", $size, $icon);
      return Array($label, $path, $tooltip);
    }

    return null;
  }

  protected final static function _getHotkeyed($doc, $pv) {
    $span = $doc->createElement("span");
    if ( ($sub = strstr($pv, "_")) !== false ) {
      $pre = str_replace($sub, "", $pv);
      $letter = substr($sub, 1, 1);
      $after = substr($sub, 2, strlen($sub));

      $inner = $doc->createElement("u");
      $inner->appendChild(new DomText($letter));

      $span->appendChild(new DomText($pre));
      $span->appendChild($inner);
      $span->appendChild(new DomText($after));
    } else {
      $span->appendChild(new DomText($pv));
    }

    return $span;
  }

  public final static function parse($file) {
    if ( file_exists($file) && ($content = file_get_contents($file)) ) {
      if ( $xml = new SimpleXMLElement($content) ) {
        return new self($file, $xml);
      }
    }

    throw new Exception("Failed to read glade file.");
  }

  public final function getWindows() {
    return $this->_aWindows;
  }

}

?>