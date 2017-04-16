<?php
session_start();

if(!isset($_SESSION['email'])) {
    header('Location: login.php');
}

$email = $_SESSION['email'];
$user = explode("@", $email)[0];
$myfile = file_get_contents($_SERVER['HOME']."/.mailsort_rules/rules_".$user.".json") or die ("Unable to open file!");
$json = json_decode($myfile);

$cnt = 0;

require('header.php');
?>
<div class="alert alert-success" role="alert" id="save_success">
    <strong>Gespeichert!</strong> Die Regeln sind automatisch aktiv.
</div>
<button type="button" class="btn btn-primary" id="btn_save">Speichern</button>
<button type="button" class="btn btn-default" id="btn_add">Regel hinzuf&uuml;gen</button>
<button type="button" class="btn btn-default" id="btn_cancel">Abbrechen</button>
<ul id="rulecards">
<?php foreach($json as $index=>$rule): ?>
<?php $cnt++; ?>
<li id="card-<?php echo $index; ?>">
            <br/>
<div class="card">
    <form id="rules-<?php echo $index; ?>">
        <div class="card-header input-group" role="tab">
            <label for="name-<?php echo $index; ?>" class="input-group-addon">Regelname</label>
            <input class="form-control" id="name-<?php echo $index; ?>" placeholder="Name" value="<?php echo $rule->name; ?>">
            <div class="input-group-addon">
                <label class="form-check-label">
                <input type="checkbox" class="form-check-input" id="active-<?php echo $index; ?>" <?php if($rule->active) echo "checked"; ?>>
                Aktiv
                </label>
            </div>
            <i class="fa fa-trash-o close input-group-addon" aria-hidden="true" id="del-rule-<?php echo $index; ?>" onclick="delRule(this);"></i>
        </div>
        <div class="card-block">
        <div class="wrapper">
        <div class="btn-group-vertical sorter">
            <button type="button" class="btn btn-secondary" onclick="moveCard(this, 'top');" id="btnTop-<?php echo $index; ?>"><i class="fa fa-angle-double-up" aria-hidden="true"></i></button>
            <button type="button" class="btn btn-secondary" onclick="moveCard(this, 'up');" id="btnUp-<?php echo $index; ?>"><i class="fa fa-angle-up" aria-hidden="true"></i></button>
            <button type="button" class="btn btn-secondary" onclick="moveCard(this, 'down');" id="btnDown-<?php echo $index; ?>"><i class="fa fa-angle-down" aria-hidden="true"></i></button>
            <button type="button" class="btn btn-secondary" onclick="moveCard(this, 'bottom');" id="btnBottom-<?php echo $index; ?>"><i class="fa fa-angle-double-down" aria-hidden="true"></i></button>
        </div>
        <div class="rules">
            <div class="form-group">
                <div class="input-group">
                    <label for="destdir-<?php echo $index; ?>" class="input-group-addon">Zielordner</label>
                    <select class="form-control" id="destdir-<?php echo $index; ?>">
                        <?php foreach($_SESSION['folders'] as $folderid=>$folder): ?>
                        <option <?php if($rule->action->destdir == explode("}", $folder)[1]) echo "selected"; ?>><?php echo explode("}", $folder)[1]; ?></option>
                        <?php endforeach ?>
                    </select>
                    <div class="input-group-addon">
                        <label class="form-check-label">
                        <input type="checkbox" class="form-check-input" id="mark_read-<?php echo $index; ?>" <?php if($rule->action->mark_read) echo "checked"; ?>>
                        Als gelesen markieren
                        </label>
                    </div>
                </div>
            </div>
            <br/>
            <div id="conditions-<?php echo $index; ?>">
            <?php foreach($rule->conditions as $conid=>$condition): ?>
            <div class="form-group" id="condition-<?php echo $index . '_' . $conid; ?>">
                <div class="input-group">
                    <label for="header-<?php echo $index . '_' . $conid; ?>" class="input-group-addon">Bedingung</label>
<select class="form-control cond-head" style="width: auto;" id="header-<?php echo $index . '_' . $conid; ?>">
                        <option <?php if($condition->header == 'Subject') echo "selected"; ?>>Subject</option>
                        <option <?php if($condition->header == 'From') echo "selected"; ?>>From</option>
                        <option <?php if($condition->header == 'To') echo "selected"; ?>>To</option>
                    </select>
                    <input class="form-control" id="regex-<?php echo $index . '_' . $conid; ?>" placeholder="Muster" value="<?php echo $condition->regex; ?>">
                    <div class="input-group-addon">
                        <label class="form-check-label">
                        <input type="checkbox" class="form-check-input" id="ignorecase-<?php echo $index . '_' . $conid; ?>" <?php if($condition->ignorecase) echo "checked"; ?>>
                        Gro&szlig;-/Kleinschreibung ignorieren
                        </label>
                    </div>
                    <i class="fa fa-trash-o close input-group-addon" aria-hidden="true" id="del-condition-<?php echo $index . '_' . $conid; ?>" onclick="delCondition(this);"></i>
                </div>
            </div>
            <?php endforeach ?>
            </div>
            <button type="button" class="btn btn-default" id="add-condition-<?php echo $index; ?>" onclick="addCondition(this);"><i class="fa fa-plus" aria-hidden="true"></i> Bedingung hinzufügen</button>
        </div>
        </div>
        </div>
    </form>
</div>
</li>
<?php endforeach; ?>
</ul>

<script>
$(document).ready(function(){
    var i=<?php echo $cnt; ?>;
    $("#save_success").hide();
    $("#btn_save").click(function() {
        var rules = [];
        $("[id^=rules]").each(function(index) {
            var ruleid = $(this).attr('id').split("-").pop();
            var rule = {};
            rule['name'] = $("#name-"+ruleid).val();
            rule['active'] = $("#active-"+ruleid)[0].checked;

            var action = {};
            action['destdir'] = $("#destdir-"+ruleid).val();
            action['mark_read'] = ($("#mark_read-"+ruleid)[0].checked);

            rule['action'] = action;

            var conditions = [];
            $("[id^=header-" + ruleid + "]").each(function(header_index) {
                var condition = {};
                condition['header'] = $("#header-" + ruleid + "_" + header_index).val();
                condition['regex'] = $("#regex-" + ruleid + "_" + header_index).val();
                condition['ignorecase'] = $("#ignorecase-" + ruleid + "_" + header_index)[0].checked;
                conditions.push(condition);
            });
            rule['conditions'] = conditions;

            rules.push(rule);
        });

        $.post("save.php", JSON.stringify(rules), function(data, status) {
            $("#save_success").fadeTo(2500, 500).slideDown(500, function(){
                    $("#save_success").slideUp(500);
            });
        });
    });


    function getCard() {
        return '<li id="card-'+i+'"><br/><div class="card">\
    <form id="rules-'+i+'">\
    <div class="card-header input-group" role="tab">\
        <label for="name-" class="input-group-addon">Regelname</label>\
        <input class="form-control" id="name-'+i+'" placeholder="Name" value="">\
        <div class="input-group-addon">\
            <label class="form-check-label">\
            <input type="checkbox" class="form-check-input" id="active-'+i+'">\
            Aktiv\
            </label>\
        </div>\
        <i class="fa fa-trash-o close input-group-addon" aria-hidden="true" id="del-rule-'+i+'" onclick="delRule(this);"></i>\
    </div>\
    <div class="card-block">\
        <div class="wrapper">\
        <div class="btn-group-vertical sorter">\
            <button type="button" class="btn btn-secondary" onclick="moveCard(this, \'top\');" id="btnTop-'+i+'"><i class="fa fa-angle-double-up" aria-hidden="true"></i></button>\
            <button type="button" class="btn btn-secondary" onclick="moveCard(this, \'up\');" id="btnUp-'+i+'"><i class="fa fa-angle-up" aria-hidden="true"></i></button>\
            <button type="button" class="btn btn-secondary" onclick="moveCard(this, \'down\');" id="btnDown-'+i+'"><i class="fa fa-angle-down" aria-hidden="true"></i></button>\
            <button type="button" class="btn btn-secondary" onclick="moveCard(this, \'bottom\');" id="btnBottom-'+i+'"><i class="fa fa-angle-double-down" aria-hidden="true"></i></button>\
        </div>\
        <div class="rules">\
        <div class="form-group">\
        <div class="input-group">\
            <label for="destdir-'+i+'" class="input-group-addon">Zielordner</label>\
            <select class="form-control" id="destdir-'+i+'">\
                <?php foreach($_SESSION['folders'] as $folderid=>$folder): ?>\
                <option><?php echo explode("}", $folder)[1]; ?></option>\
                <?php endforeach ?>\
            </select>\
            <div class="input-group-addon">\
                <label class="form-check-label">\
                <input type="checkbox" class="form-check-input" id="mark_read-'+i+'">\
                Als gelesen markieren\
                </label>\
            </div>\
        </div>\
            </div>\
                <br/>\
            <div id="conditions-'+i+'">\
        <div class="form-group" id="condition-'+i+'_0">\
            <div class="input-group">\
                <label for="header-'+i+'_0" class="input-group-addon">Bedingung</label>\
                <select class="form-control cond-head" id="header-'+i+'_0">\
                    <option>Subject</option>\
                    <option>From</option>\
                    <option>To</option>\
                </select>\
                <input class="form-control" id="regex-'+i+'_0" placeholder="Muster" value="">\
                <div class="input-group-addon">\
                    <label class="form-check-label">\
                    <input type="checkbox" class="form-check-input" id="ignorecase-'+i+'_0">\
                    Gro&szlig;-/Kleinschreibung ignorieren\
                    </label>\
                </div>\
                <i class="fa fa-trash-o close input-group-addon" aria-hidden="true" id="del-condition-'+i+'_0" onclick="delCondition(this);"></i>\
            </div>\
                </div>\
                    </div>\
            <button type="button" class="btn btn-default" id="add-condition-'+i+'" onclick="addCondition(this);"><i class="fa fa-plus" aria-hidden="true"></i> Bedingung hinzufügen</button>\
    </div>\
    </div>\
    </div>\
    </form>\
    </div>\
        </li>'
    }
    $("#btn_add").click(function () {
        $("#rulecards").append(getCard());
        i++;
    });
    $("#btn_cancel").click(function () {
        location.reload();
    });
});
function delRule(obj) {
    delid = obj.id.split("-").pop();
    $("#card-" + delid).remove();
}
function delCondition(obj) {
    delid = obj.id.split("-").pop();
    $("#condition-" + delid).remove();
}
var condadd=0;
function addCondition(obj) {
    addid = obj.id.split("-").pop();
    newid = addid + "_n" + condadd;
    $("#conditions-" + addid).append('<div class="form-group" id="condition-'+newid+'">\
                <div class="input-group">\
                    <label for="header-'+newid+'" class="input-group-addon">Bedingung</label>\
                    <select class="form-control cond-head" id="header-'+newid+'">\
                        <option selected>Subject</option>\
                        <option>From</option>\
                        <option>To</option>\
                    </select>\
                    <input class="form-control" id="regex-'+newid+'" placeholder="Muster" value="<?php echo $condition->regex; ?>">\
                    <div class="input-group-addon">\
                        <label class="form-check-label">\
                        <input type="checkbox" class="form-check-input" id="ignorecase-'+newid+'" <?php if($condition->ignorecase) echo "checked"; ?>>\
                        Gro&szlig;-/Kleinschreibung ignorieren\
                        </label>\
                    </div>\
                    <i class="fa fa-trash-o close input-group-addon" aria-hidden="true" id="del-condition-'+newid+'" onclick="delCondition(this);"></i>\
                </div>\
            </div>');
}
function moveCard(obj, direction) {
    moveid = obj.id.split("-").pop();
    currentCard = $("#card-" + moveid);
    if(direction == "up") {
        currentCard.prev().before(currentCard);
    }
    else if(direction == "down") {
        currentCard.next().after(currentCard);
    }
    else if(direction == "top") {
        $("#rulecards li").first().before(currentCard);
    }
    else if(direction == "bottom") {
        $("#rulecards li").last().after(currentCard);
    }
}
</script>

<?php require('footer.php');
