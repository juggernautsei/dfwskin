<?php
/**
* Review of Systems Checks form
*
* @package   OpenEMR
* @link      http://www.open-emr.org
* @author    sunsetsystems <sunsetsystems>
* @author    cfapress <cfapress>
* @author    Brady Miller <brady.g.miller@gmail.com>
* @copyright Copyright (c) 2009 sunsetsystems <sunsetsystems>
* @copyright Copyright (c) 2008 cfapress <cfapress>
* @copyright Copyright (c) 2016-2019 Brady Miller <brady.g.miller@gmail.com>
* @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
*/


require_once("../../globals.php");
require_once("$srcdir/api.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$returnurl = 'encounter_top.php';
?>
<html>
<head>
    <title><?php echo xlt("Review of Systems Checks"); ?></title>

    <?php Header::setupHeader();?>
</head>
<body>
<div class="container mt-3">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h2><?php echo xlt("Review of Systems Checks");?></h2>
            </div>
        </div>
    </div>
    <div class="row">
        <form method=post action="<?php echo $rootdir;?>/forms/reviewofs/save.php?mode=new" name="my_form" onsubmit="return top.restoreSession()">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
            <fieldset> <!--ALB Put the button for notes here -->
                    <legend><?php echo xlt('Notes');?></legend>
                        <div class="form-group">
                            <div class="col-sm-10 col-sm-offset-1">
                                <button class="btn btn-primary" id="textload">Negative ROS</button>
                            </div>
                            <div class="col-sm-10 col-sm-offset-1">
                                <textarea name="additional_notes" class="form-control" cols="80" rows="5" id="field1"></textarea>
                            </div>
                        </div>
            </fieldset>
            <fieldset>
                <legend><?php echo xlt('General')?></legend>
                <div class="row"> <!--ALB Took out a lot of formatting here and all sections below -->
                     <div class="col-lg-12">
                                        <input type="checkbox" name='fever'> <?php echo xlt('Fever');?>
                                        <input type="checkbox" name='chills'> <?php echo xlt('Chills');?>
                                        <input type="checkbox" name='night_sweats'> <?php echo xlt('Night Sweats');?>
                                        <input type="checkbox" name='weight_loss'> <?php echo xlt('Weight Loss');?>
                                        <input type="checkbox" name='poor_appetite'> <?php echo xlt('Poor Appetite');?>
                                        <input type="checkbox" name='insomnia'> <?php echo xlt('Insomnia');?>
                     </div><br>
                     <div class="col-lg-12">
                                        <input type="checkbox" name='fatigued'> <?php echo xlt('Fatigue');?>
                                        <input type="checkbox" name='depressed'> <?php echo xlt('Depression');?>
                                        <input type="checkbox" name='hyperactive'> <?php echo xlt('Anxiety');?>
                                        <input type="checkbox" name='exposure_to_foreign_countries'> <?php echo xlt('Recent Travel to Foreign Countries');?>

                     </div>
                </div><br>
            </fieldset>
            <fieldset>
                <legend><?php echo xlt('Skin')?></legend>
                <div class="row">
                     <div class="col-lg-12">
                                        <input type="checkbox" name='rashes'> <?php echo xlt('Rashes');?>
                                         <input type="checkbox" name='infections'> <?php echo xlt('Infections');?>
                                         <input type="checkbox" name='ulcerations'> <?php echo xlt('Ulcerations');?>
                                        <input type="checkbox" name='pemphigus'> <?php echo xlt('Blisters');?>
                                        <input type="checkbox" name='herpes'> <?php echo xlt('Herpes/Cold Sores');?>
                    </div>
                </div><br>
            </fieldset>
            <fieldset>
                <legend><?php echo xlt('HEENT')?></legend>
                <div class="row">
                     <div class="col-lg-12">
                                        <input type="checkbox" name='cataracts'> <?php echo xlt('Cataracts');?>
                                        <input type="checkbox" name='cataract_surgery'> <?php echo xlt('Cataract Surgery');?>
                                        <input type="checkbox" name='glaucoma'> <?php echo xlt('Glaucoma');?>
                                         <input type="checkbox" name='double_vision'> <?php echo xlt('Double Vision');?>
                                        <input type="checkbox" name='blurred_vision'> <?php echo xlt('Blurred Vision');?>
                                        <input type="checkbox" name='poor_hearing'> <?php echo xlt('Poor Hearing');?>
                                        <input type="checkbox" name='headaches'> <?php echo xlt('Headaches');?>
                                        <input type="checkbox" name='ringing_in_ears'> <?php echo xlt('Ringing in Ears');?>
                                        <input type="checkbox" name='bloody_nose'> <?php echo xlt('Bloody Nose');?>
 
                    </div><br>
                     <div class="col-lg-12">
                                       <input type="checkbox" name='sinusitis'> <?php echo xlt('Sinusitis');?>
                                        <input type="checkbox" name='sinus_surgery'> <?php echo xlt('Sinus Surgery');?>
                                        <input type="checkbox" name='dry_mouth'> <?php echo xlt('Dry Mouth');?>
                                        <input type="checkbox" name='strep_throat'> <?php echo xlt('Strep Throat');?>
                                        <input type="checkbox" name='tonsillectomy'> <?php echo xlt('Tonsillectomy');?>
                                        <input type="checkbox" name='swollen_lymph_nodes'> <?php echo xlt('Swollen Lymph Nodes');?>
                                         <input type="checkbox" name='throat_cancer'> <?php echo xlt('Throat Cancer');?>
                                         <input type="checkbox" name='throat_cancer_surgery'> <?php echo xlt('Throat Cancer Surgery');?>
                     </div>
                </div><br>
            </fieldset>
            <fieldset>
                <legend><?php echo xlt('Pulmonary')?></legend>
                <div class="row">
                     <div class="col-lg-12">
                                         <input type="checkbox" name='emphysema'> <?php echo xlt('Emphysema');?>
                                         <input type="checkbox" name='chronic_bronchitis'> <?php echo xlt('Chronic Bronchitis');?>
                                        <input type="checkbox" name='interstitial_lung_disease'> <?php echo xlt('Interstitial Lung Disease');?>
                                         <input type="checkbox" name='shortness_of_breath_2'> <?php echo xlt('Shortness of Breath');?>
                                        <input type="checkbox" name='lung_cancer'> <?php echo xlt('Lung Cancer');?>
                                         <input type="checkbox" name='lung_cancer_surgery'> <?php echo xlt('Lung Cancer Surgery');?>
                                        <input type="checkbox" name='pheumothorax'> <?php echo xlt('Pheumothorax');?>
                    </div>
                </div><br>
            </fieldset>
            <fieldset>
                <legend><?php echo xlt('Cardiovascular')?></legend>
                <div class="row">
                     <div class="col-lg-12">
                                         <input type="checkbox" name='heart_attack'> <?php echo xlt('Heart Attack');?>
                                         <input type="checkbox" name='irregular_heart_beat'> <?php echo xlt('Irregular Heart Beat');?>
                                         <input type="checkbox" name='chest_pains'> <?php echo xlt('Chest Pains');?>
                                         <input type="checkbox" name='shortness_of_breath'> <?php echo xlt('Shortness of Breath');?>
                                         <input type="checkbox" name='high_blood_pressure'> <?php echo xlt('High Blood Pressure');?>
                                         <input type="checkbox" name='heart_failure'> <?php echo xlt('Heart Failure');?>
                                         <input type="checkbox" name='poor_circulation'> <?php echo xlt('Poor Circulation');?>
                     </div><br>
                     <div class="col-lg-12">
                                         <input type="checkbox" name='vascular_surgery'> <?php echo xlt('Vascular Surgery');?>
                                         <input type="checkbox" name='cardiac_catheterization'> <?php echo xlt('Cardiac Catheterization');?>
                                         <input type="checkbox" name='coronary_artery_bypass'> <?php echo xlt('Coronary Artery Bypass');?>
                                        <input type="checkbox" name='heart_transplant'> <?php echo xlt('Heart Transplant');?>
                                         <input type="checkbox" name='stress_test'> <?php echo xlt('Stress Test');?>
                     </div>
                </div><br>
            </fieldset>
            <fieldset>
                <legend><?php echo xlt('Gastrointestinal')?></legend>
                <div class="row">
                     <div class="col-lg-12">
                                        <input type="checkbox" name='stomach_pains'> <?php echo xlt('Stomach Pains');?>
                                        <input type="checkbox" name='peptic_ulcer_disease'> <?php echo xlt('Peptic Ulcer Disease');?>
                                        <input type="checkbox" name='gastritis'> <?php echo xlt('Gastritis');?>
                                         <input type="checkbox" name='endoscopy'> <?php echo xlt('Endoscopy');?>
                                         <input type="checkbox" name='polyps'> <?php echo xlt('Polyps');?>
                                       <input type="checkbox" name='colonoscopy'> <?php echo xlt('Colonoscopy');?>
                                         <input type="checkbox" name='colon_cancer'> <?php echo xlt('Colon Cancer');?>
                                        <input type="checkbox" name='colon_cancer_surgery'> <?php echo xlt('Colon Cancer Surgery');?>
                                         <input type="checkbox" name='ulcerative_colitis'> <?php echo xlt('Ulcerative Colitis');?>
                    </div><br>
                     <div class="col-lg-12">
                                        <input type="checkbox" name='crohns_disease'> <?php echo xlt('Crohn\'s Disease');?>
                                         <input type="checkbox" name='appendectomy'> <?php echo xlt('Appendectomy');?>
                                        <input type="checkbox" name='divirticulitis'> <?php echo xlt('Diverticulitis');?>
                                        <input type="checkbox" name='divirticulitis_surgery'> <?php echo xlt('Diverticulitis Surgery');?>
                                        <input type="checkbox" name='gall_stones'> <?php echo xlt('Gall Stones');?>
                                         <input type="checkbox" name='cholecystectomy'> <?php echo xlt('Cholecystectomy');?>
                                        <input type="checkbox" name='hepatitis'> <?php echo xlt('Hepatitis');?>
                                        <input type="checkbox" name='cirrhosis_of_the_liver'> <?php echo xlt('Cirrhosis of the Liver');?>
                                        <input type="checkbox" name='splenectomy'> <?php echo xlt('Splenectomy');?>
                    </div>
                </div><br>
            </fieldset>
            <fieldset>
                <legend><?php echo xlt('Genitourinary')?></legend>
                <div class="row">
                     <div class="col-lg-12">
                                        <input type="checkbox" name='kidney_failure'> <?php echo xlt('Kidney Failure');?>
                                        <input type="checkbox" name='kidney_stones'> <?php echo xlt('Kidney Stones');?>
                                         <input type="checkbox" name='kidney_cancer'> <?php echo xlt('Kidney Cancer');?>
                                        <input type="checkbox" name='kidney_infections'> <?php echo xlt('Kidney Infections');?>
                                        <input type="checkbox" name='bladder_infections'> <?php echo xlt('Bladder Infections');?>
                                        <input type="checkbox" name='bladder_cancer'> <?php echo xlt('Bladder Cancer');?>
                                        <input type="checkbox" name='prostate_problems'> <?php echo xlt('Prostate Problems');?>
                                        <input type="checkbox" name='prostate_cancer'> <?php echo xlt('Prostate Cancer');?>
                    </div><br>
                     <div class="col-lg-12">
                                        <input type="checkbox" name='kidney_transplant'> <?php echo xlt('Kidney Transplant');?>
                                        <input type="checkbox" name='sexually_transmitted_disease'> <?php echo xlt('Sexually Transmitted Disease');?>
                                        <input type="checkbox" name='burning_with_urination'> <?php echo xlt('Burning with Urination');?>
                                        <input type="checkbox" name='discharge_from_urethra'> <?php echo xlt('Discharge From Urethra');?>
                    </div>
                </div><br>
            </fieldset>
            <fieldset>
                <legend><?php echo xlt('Musculoskeletal')?></legend>
                <div class="row">
                     <div class="col-lg-12">
                                        <input type="checkbox" name='osetoarthritis'> <?php echo xlt('Osetoarthritis');?>
                                        <input type="checkbox" name='rheumotoid_arthritis'> <?php echo xlt('Rheumatoid Arthritis');?>
                                        <input type="checkbox" name='lupus'> <?php echo xlt('Lupus');?>
                                         <input type="checkbox" name='ankylosing_sondlilitis'> <?php echo xlt('Ankylosing Spondilitis');?>
                                        <input type="checkbox" name='swollen_joints'> <?php echo xlt('Swollen Joints');?>
                                          <input type="checkbox" name='stiff_joints'> <?php echo xlt('Stiff Joints');?>
                                        <input type="checkbox" name='broken_bones'> <?php echo xlt('Broken Bones');?>
                                        <input type="checkbox" name='neck_problems'> <?php echo xlt('Neck Problems');?>
                                        <input type="checkbox" name='back_problems'> <?php echo xlt('Back Problems');?>
                                        <input type="checkbox" name='back_surgery'> <?php echo xlt('Back Surgery');?>
                    </div><br>
                     <div class="col-lg-12">
                                        <input type="checkbox" name='scoliosis'> <?php echo xlt('Scoliosis');?>
                                        <input type="checkbox" name='herniated_disc'> <?php echo xlt('Herniated Disc');?>
                                        <input type="checkbox" name='shoulder_problems'> <?php echo xlt('Shoulder Problems');?>
                                        <input type="checkbox" name='elbow_problems'> <?php echo xlt('Elbow Problems');?>
                                        <input type="checkbox" name='wrist_problems'> <?php echo xlt('Wrist Problems');?>
                                        <input type="checkbox" name='hand_problems'> <?php echo xlt('Hand Problems');?>
                                        <input type="checkbox" name='hip_problems'> <?php echo xlt('Hip Problems');?>
                                        <input type="checkbox" name='knee_problems'> <?php echo xlt('Knee Problems');?>
                                        <input type="checkbox" name='ankle_problems'> <?php echo xlt('Ankle Problems');?>
                                        <input type="checkbox" name='foot_problems'> <?php echo xlt('Foot Problems');?>
                    </div>
                </div><br>
            </fieldset>
            <fieldset>
                <legend><?php echo xlt('Endocrine')?></legend>
                <div class="row">
                     <div class="col-lg-12">
                                        <input type="checkbox" name='insulin_dependent_diabetes'> <?php echo xlt('Insulin Dependent Diabetes');?>
                                        <input type="checkbox" name='noninsulin_dependent_diabetes'> <?php echo xlt('Non-Insulin Dependent Diabetes');?>
                                         <input type="checkbox" name='hypothyroidism'> <?php echo xlt('Hypothyroidism');?>
                                        <input type="checkbox" name='hyperthyroidism'> <?php echo xlt('Hyperthyroidism');?>
                                         <input type="checkbox" name='cushing_syndrom'> <?php echo xlt('Cushing Syndrome');?>
                                        <input type="checkbox" name='addison_syndrom'> <?php echo xlt('Addison Syndrome');?>
                    </div>
                </div><br>
            </fieldset>
                <div class="form-group clearfix">
                    <div class="col-sm-12 col-sm-offset-1 position-override">
                        <div class="btn-group oe-opt-btn-group-pinch" role="group">
                        <button type="submit" onclick="top.restoreSession()" class="btn btn-default btn-save"><?php echo xlt('Save'); ?></button>
                        <button type="button" class="btn btn-link btn-cancel oe-opt-btn-separate-left" onclick="top.restoreSession(); parent.closeTab(window.name, false);"><?php echo xlt('Cancel');?></button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
    const neg = document.getElementById('textload').addEventListener('click', loadText);
    function loadText(e) {
        document.getElementById('field1').value = 'No fever, chills, nausea, vomiting, diarrhea, shortness of breath, chest pain, headache, joint pains.';
        e.preventDefault();
    }
</script>
</body>
</html>
