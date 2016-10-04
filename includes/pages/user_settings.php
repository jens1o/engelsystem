<?php

function settings_title() {
  return _("Settings");
}

function user_settings() {
  global $enable_tshirt_size, $tshirt_sizes, $themes, $locales;
  global $user;
  
  $msg = "";
  $nick = $user['Nick'];
  $lastname = $user['Name'];
  $prename = $user['Vorname'];
  $age = $user['Alter'];
  $tel = $user['Telefon'];
  $dect = $user['DECT'];
  $mobile = $user['Handy'];
  $mail = $user['email'];
  $email_shiftinfo = $user['email_shiftinfo'];
  $jabber = $user['jabber'];
  $hometown = $user['Hometown'];
  $tshirt_size = $user['Size'];
  $selected_theme = $user['color'];
  $selected_language = $user['Sprache'];
  $planned_arrival_date = $user['planned_arrival_date'];
  $planned_departure_date = $user['planned_departure_date'];
  
  if (isset($_REQUEST['submit'])) {
    $valid = true;
    
    if (isset($_REQUEST['mail']) && strlen(strip_request_item('mail')) > 0) {
      $mail = strip_request_item('mail');
      if (! check_email($mail)) {
        $valid = false;
        $msg .= error(_("E-mail address is not correct."), true);
      }
    } else {
      $valid = false;
      $msg .= error(_("Please enter your e-mail."), true);
    }
    
    $email_shiftinfo = isset($_REQUEST['email_shiftinfo']);
    
    if (isset($_REQUEST['jabber']) && strlen(strip_request_item('jabber')) > 0) {
      $jabber = strip_request_item('jabber');
      if (! check_email($jabber)) {
        $valid = false;
        $msg .= error(_("Please check your jabber account information."), true);
      }
    }
    
    if (isset($_REQUEST['tshirt_size']) && isset($tshirt_sizes[$_REQUEST['tshirt_size']])) {
      $tshirt_size = $_REQUEST['tshirt_size'];
    } elseif ($enable_tshirt_size) {
      $valid = false;
    }
    
    if (isset($_REQUEST['planned_arrival_date']) && $tmp = parse_date("Y-m-d", $_REQUEST['planned_arrival_date'])) {
      $planned_arrival_date = $tmp;
    } else {
      $valid = false;
      $msg .= error(_("Please enter your planned date of arrival."), true);
    }
    
    if (isset($_REQUEST['planned_departure_date']) && $_REQUEST['planned_departure_date'] != '') {
      if ($tmp = parse_date("Y-m-d", $_REQUEST['planned_departure_date'])) {
        $planned_departure_date = $tmp;
      } else {
        $valid = false;
        $msg .= error(_("Please enter your planned date of departure."), true);
      }
    } else {
      $planned_departure_date = null;
    }
    
    // Trivia
    if (isset($_REQUEST['lastname'])) {
      $lastname = strip_request_item('lastname');
    }
    if (isset($_REQUEST['prename'])) {
      $prename = strip_request_item('prename');
    }
    if (isset($_REQUEST['age']) && preg_match("/^[0-9]{0,4}$/", $_REQUEST['age'])) {
      $age = strip_request_item('age');
    }
    if (isset($_REQUEST['tel'])) {
      $tel = strip_request_item('tel');
    }
    if (isset($_REQUEST['dect'])) {
      $dect = strip_request_item('dect');
    }
    if (isset($_REQUEST['mobile'])) {
      $mobile = strip_request_item('mobile');
    }
    if (isset($_REQUEST['hometown'])) {
      $hometown = strip_request_item('hometown');
    }
    
    if ($valid) {
      sql_query("
          UPDATE `User` SET
          `Nick`='" . sql_escape($nick) . "',
          `Vorname`='" . sql_escape($prename) . "',
          `Name`='" . sql_escape($lastname) . "',
          `Alter`='" . sql_escape($age) . "',
          `Telefon`='" . sql_escape($tel) . "',
          `DECT`='" . sql_escape($dect) . "',
          `Handy`='" . sql_escape($mobile) . "',
          `email`='" . sql_escape($mail) . "',
          `email_shiftinfo`=" . sql_bool($email_shiftinfo) . ",
          `jabber`='" . sql_escape($jabber) . "',
          `Size`='" . sql_escape($tshirt_size) . "',
          `Hometown`='" . sql_escape($hometown) . "',
          `planned_arrival_date`='" . sql_escape($planned_arrival_date) . "',
          `planned_departure_date`=" . sql_null($planned_departure_date) . "
          WHERE `UID`='" . sql_escape($user['UID']) . "'");
      
      success(_("Settings saved."));
      redirect(page_link_to('user_settings'));
    }
  } elseif (isset($_REQUEST['submit_password'])) {
    $valid = true;
    
    if (! isset($_REQUEST['password']) || ! verify_password($_REQUEST['password'], $user['Passwort'], $user['UID'])) {
      $msg .= error(_("-> not OK. Please try again."), true);
    } elseif (strlen($_REQUEST['new_password']) < MIN_PASSWORD_LENGTH) {
      $msg .= error(_("Your password is to short (please use at least 6 characters)."), true);
    } elseif ($_REQUEST['new_password'] != $_REQUEST['new_password2']) {
      $msg .= error(_("Your passwords don't match."), true);
    } elseif (set_password($user['UID'], $_REQUEST['new_password'])) {
      success(_("Password saved."));
    } else {
      error(_("Failed setting password."));
    }
    redirect(page_link_to('user_settings'));
  } elseif (isset($_REQUEST['submit_theme'])) {
    $valid = true;
    
    if (isset($_REQUEST['theme']) && isset($themes[$_REQUEST['theme']])) {
      $selected_theme = $_REQUEST['theme'];
    } else {
      $valid = false;
    }
    
    if ($valid) {
      sql_query("UPDATE `User` SET `color`='" . sql_escape($selected_theme) . "' WHERE `UID`='" . sql_escape($user['UID']) . "'");
      
      success(_("Theme changed."));
      redirect(page_link_to('user_settings'));
    }
  } elseif (isset($_REQUEST['submit_language'])) {
    $valid = true;
    
    if (isset($_REQUEST['language']) && isset($locales[$_REQUEST['language']])) {
      $selected_language = $_REQUEST['language'];
    } else {
      $valid = false;
    }
    
    if ($valid) {
      sql_query("UPDATE `User` SET `Sprache`='" . sql_escape($selected_language) . "' WHERE `UID`='" . sql_escape($user['UID']) . "'");
      $_SESSION['locale'] = $selected_language;
      
      success("Language changed.");
      redirect(page_link_to('user_settings'));
    }
  }
  
  return page_with_title(settings_title(), [
      $msg,
      msg(),
      div('row', [
          div('col-md-6', [
              form([
                  form_info('', _("Here you can change your user details.")),
                  form_info(entry_required() . ' = ' . _("Entry required!")),
                  form_text('nick', _("Nick"), $nick, true),
                  form_text('lastname', _("Last name"), $lastname),
                  form_text('prename', _("First name"), $prename),
                  form_date('planned_arrival_date', _("Planned date of arrival") . ' ' . entry_required(), $planned_arrival_date, time()),
                  form_date('planned_departure_date', _("Planned date of departure"), $planned_departure_date, time()),
                  form_text('age', _("Age"), $age),
                  form_text('tel', _("Phone"), $tel),
                  form_text('dect', _("DECT"), $dect),
                  form_text('mobile', _("Mobile"), $mobile),
                  form_text('mail', _("E-Mail") . ' ' . entry_required(), $mail),
                  form_checkbox('email_shiftinfo', _("Please send me an email if my shifts change"), $email_shiftinfo),
                  form_text('jabber', _("Jabber"), $jabber),
                  form_text('hometown', _("Hometown"), $hometown),
                  $enable_tshirt_size ? form_select('tshirt_size', _("Shirt size"), $tshirt_sizes, $tshirt_size) : '',
                  form_info('', _('Please visit the angeltypes page to manage your angeltypes.')),
                  form_submit('submit', _("Save")) 
              ]) 
          ]),
          div('col-md-6', [
              form([
                  form_info(_("Here you can change your password.")),
                  form_password('password', _("Old password:")),
                  form_password('new_password', _("New password:")),
                  form_password('new_password2', _("Password confirmation:")),
                  form_submit('submit_password', _("Save")) 
              ]),
              form([
                  form_info(_("Here you can choose your color settings:")),
                  form_select('theme', _("Color settings:"), $themes, $selected_theme),
                  form_submit('submit_theme', _("Save")) 
              ]),
              form([
                  form_info(_("Here you can choose your language:")),
                  form_select('language', _("Language:"), $locales, $selected_language),
                  form_submit('submit_language', _("Save")) 
              ]) 
          ]) 
      ]) 
  ]);
}
?>
