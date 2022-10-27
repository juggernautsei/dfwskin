<?php /* Smarty version 2.6.33, created on 2022-10-22 14:29:42
         compiled from /var/www/html/ehrv7/interface/forms/soap/templates/general_new.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'xlt', '/var/www/html/ehrv7/interface/forms/soap/templates/general_new.html', 3, false),array('function', 'headerTemplate', '/var/www/html/ehrv7/interface/forms/soap/templates/general_new.html', 4, false),array('modifier', 'attr', '/var/www/html/ehrv7/interface/forms/soap/templates/general_new.html', 18, false),array('modifier', 'text', '/var/www/html/ehrv7/interface/forms/soap/templates/general_new.html', 49, false),)), $this); ?>
<html>
<head>
    <title><?php echo smarty_function_xlt(array('t' => 'SOAP'), $this);?>
</title>
    <?php echo smarty_function_headerTemplate(array(), $this);?>

</head>
<body class="body_top">
<div class="container">
    <div class="row">
        <div class="col-xs-12">
            <div class="page-header">
                <h2><?php echo smarty_function_xlt(array('t' => 'SOAP'), $this);?>
</h2>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <form name="soap" method="post" action="<?php echo $this->_tpl_vars['FORM_ACTION']; ?>
/interface/forms/soap/save.php" onsubmit="return top.restoreSession()">
                <input type="hidden" name="csrf_token_form" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['CSRF_TOKEN_FORM'])) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
">
                <fieldset>
                    <div class="form-group">
                        <input type="button" value="Problem" onclick="document.getElementById('field1').value=document.getElementById('field1').value+'Patient is here for  , which started  ago, worse x . Associated symptoms: . Previous treatments: . '"/>

                        <input type="button" value="Mohs Consult" onclick="document.getElementById('field1').value=document.getElementById('field1').value+'Patient is here for Mohs consult for biopsy-proven  on  . Consult from  . Biopsy done on . '"/>

                        <input type="button" value="Skin check" onclick="document.getElementById('field1').value=document.getElementById('field1').value+'Patient is here for a skin check. No personal or family history of skin cancer. Pt with history of excessive sun exposure. No new complaints. '"/>

                        <input type="button" value="Mohs Surgery" onclick="document.getElementById('field1').value=document.getElementById('field1').value+'Patient is here for Mohs surgery.', document.getElementById('field4').value=document.getElementById('field4').value+'See Mohs op report.'"/>

                        <input type="button" value="Excision" onclick="document.getElementById('field1').value=document.getElementById('field1').value+'Patient is here for excision for biopsy-proven  on  . Consult from  . Biopsy done on . ',
                                document.getElementById('field4').value=document.getElementById('field4').value+'Excision today. See operative report.\r\n'"/>

                        <input type="button" value="S/R" onclick="document.getElementById('field1').value=document.getElementById('field1').value+'Patient is here for suture removal status post  for  on  performed on . Original consult from  . No pain/fever/discharge.',
                                document.getElementById('field2').value=document.getElementById('field2').value+'Wound clean/dry/intact.',
                                document.getElementById('field4').value=document.getElementById('field4').value+'Sutures removed. Continue wound care. F/U in 6 weeks.\r\n'"/>

                        <input type="button" value="W/C" onclick="document.getElementById('field1').value=document.getElementById('field1').value+'Patient is here for wound check status post  for  on  performed on . Original consult from  . No pain/fever/discharge.',
                                document.getElementById('field2').value=document.getElementById('field2').value+'Wound healing well.',
                                document.getElementById('field4').value=document.getElementById('field4').value+'Healing well. Continue wound care. F/U in 6 weeks.\r\n'"/>

                        <input type="button" value="High BP" onclick="document.getElementById('field1').value=document.getElementById('field1').value+'Patient came in for Mohs surgery. However, his blood pressure was found to be elevated ( , , , ). Patient denied chest pain, shortness of breath, headache, double vision, or abdominal pain.',
                                document.getElementById('field4').value=document.getElementById('field4').value+'Blood pressure did not improve after various relaxation techniques. PCP was contacted and patient will be seen . If any of the above symptoms occur before his visit, patient to go to the ER immediately. Mohs surgery was rescheduled.\r\n'"/>

<input type="button" value="Tele Consult" onclick="document.getElementById('field1').value=document.getElementById('field1').value+'Teledermatology consultation. Patient initiated a consultation through 2-way synchronous video conferencing. Patient was referred for Mohs consult for biopsy-proven  on  . Consult from  . Biopsy done on . '"/>

                    </div>
                    <legend><?php echo smarty_function_xlt(array('t' => 'Subjective'), $this);?>
</legend>
                    <div class="form-group" >
                        <div class="col-sm-10 col-sm-offset-1">
                            <textarea name="subjective" class="form-control" id="field1" cols="60" rows="6"><?php echo ((is_array($_tmp=$this->_tpl_vars['data']->get_subjective())) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
</textarea>
                        </div>
                    </div>
                </fieldset>
                <fieldset>
                    <div class="form-group">
                        <input type="button" value="General/F" onclick="document.getElementById('field2').value=document.getElementById('field2').value+'Patient is in no acute distress, alert and oriented x3, well-developed, well-nourished. Patient examined in the presence of a female medical assistant.\r\n'"/>

                        <input type="button" value="General/M" onclick="document.getElementById('field2').value=document.getElementById('field2').value+'Patient is in no acute distress, alert and oriented x3, well-developed, well-nourished.\r\n'"/>

                        <input type="button" value="General/Child" onclick="document.getElementById('field2').value=document.getElementById('field2').value+'Patient is in no acute distress, alert, well-nourished. Patient examined in the presence of a female medical assistant.\r\n'"/>

                        <input type="button" value="TBSE" onclick="document.getElementById('field2').value=document.getElementById('field2').value+'TBSE today. Areas examined: scalp, face, conjunctivae/eyelids, neck, lips, oral mucosa, chest/breasts/axillae, back, abdomen, right upper extremity, left upper extremity, right lower extremity, left lower extremity, digits/nails, lymphatic, peripheral vascular. All areas examined were found to be within normal limit or clinically insignificant except: '"/>

                        <input type="button" value="No TBSE" onclick="document.getElementById('field2').value=document.getElementById('field2').value+'Patient prefers focal examination only today.\r\n'"/>

                        <input type="button" value="Wound c/d/i" onclick="document.getElementById('field2').value=document.getElementById('field2').value+'Wound clean, dry, intact.\r\n'"/>

                    </div>
                    <div class="form-group">
                        <input type="button" value="BCC" onclick="document.getElementById('field2').value=document.getElementById('field2').value+'approximately cm pearly papule.\r\n'"/>

                        <input type="button" value="BCC/large" onclick="document.getElementById('field2').value=document.getElementById('field2').value+'approximately cm pearly plaque.\r\n'"/>

                        <input type="button" value="SCC" onclick="document.getElementById('field2').value=document.getElementById('field2').value+'approximately cm erythematous hyperkeratotic papule.\r\n'"/>

                        <input type="button" value="SCC/large" onclick="document.getElementById('field2').value=document.getElementById('field2').value+'approximately cm erythematous hyperkeratotic plaque.\r\n'"/>

                        <input type="button" value="PDB" onclick="document.getElementById('field2').value=document.getElementById('field2').value+' with poorly-defined borders.\r\n'"/>

                        <input type="button" value="AK" onclick="document.getElementById('field2').value=document.getElementById('field2').value+'erythematous rough scaly patch.\r\n'"/>

                        <input type="button" value="AKs" onclick="document.getElementById('field2').value=document.getElementById('field2').value+'erythematous rough scaly patches.\r\n'"/>

                        <input type="button" value="SK" onclick="document.getElementById('field2').value=document.getElementById('field2').value+'stuck-on keratosis.\r\n'"/>

                        <input type="button" value="SKs" onclick="document.getElementById('field2').value=document.getElementById('field2').value+'stuck-on keratoses.\r\n'"/>

                        <input type="button" value="Nevus" onclick="document.getElementById('field2').value=document.getElementById('field2').value+'brown macule with regular borders and color.\r\n'"/>

                        <input type="button" value="Atypical Nevus/MM" onclick="document.getElementById('field2').value=document.getElementById('field2').value+'brown macule with irregular borders and color.\r\n'"/>

                        <input type="button" value="VV" onclick="document.getElementById('field2').value=document.getElementById('field2').value+'verrucous papule.\r\n'"/>
                    </div>
                    <div class="form-group">
                        <input type="button" value="Photodamage" onclick="document.getElementById('field2').value=document.getElementById('field2').value+'Photodamaged skin.\r\n'"/>

                        <input type="button" value="No Recurrence" onclick="document.getElementById('field2').value=document.getElementById('field2').value+'Above locations with no evidence of recurrence.\r\n'"/>

                        <input type="button" value="Scar/NR" onclick="document.getElementById('field2').value=document.getElementById('field2').value+'scar, no evidence of recurrence on examination or palpation.\r\n'"/>

                        <input type="button" value="No LAD" onclick="document.getElementById('field2').value=document.getElementById('field2').value+'No cervical/axillary/femoral lymphadenopathy.\r\n'"/>
                    </div>
                    <div class="form-group">
<input type="button" value="DF" onclick="document.getElementById('field2').value=document.getElementById('field2').value+'fibrous nodule with positive dimple sign.\r\n'"/>

<input type="button" value="Numm Derm" onclick="document.getElementById('field2').value=document.getElementById('field2').value+'round/oval erythematous scaly thin plaques.\r\n'"/>

<input type="button" value="Seb Derm" onclick="document.getElementById('field2').value=document.getElementById('field2').value+'erythematous patches and plaques with scale.\r\n'"/>

<input type="button" value="Psoriasis" onclick="document.getElementById('field2').value=document.getElementById('field2').value+'erythematous, round to ovoid papulosquamous plaques with silvery scales.\r\n'"/>

<input type="button" value="Cyst" onclick="document.getElementById('field2').value=document.getElementById('field2').value+'approximately cm, freely moveable, subcutaneous nodule with central punctum.\r\n'"/>

<input type="button" value="Melasma" onclick="document.getElementById('field2').value=document.getElementById('field2').value+'approximately 1-5 mm, tan to hyperpigmented macules.\r\n'"/>

<input type="button" value="Rosacea" onclick="document.getElementById('field2').value=document.getElementById('field2').value+'telangiectasias, papules, and pustules on an erythematous background.\r\n'"/>

<input type="button" value="Skin tag" onclick="document.getElementById('field2').value=document.getElementById('field2').value+'flesh toned or brown soft, pedunculated papule.\r\n'"/>

<input type="button" value="Folliculitis" onclick="document.getElementById('field2').value=document.getElementById('field2').value+'erythematous, follicular based papules and pustules.\r\n'"/>

<input type="button" value="KP" onclick="document.getElementById('field2').value=document.getElementById('field2').value+'hyperkeratotic, follicular based papules on an erythematous background.\r\n'"/>

<input type="button" value="Hair loss" onclick="document.getElementById('field2').value=document.getElementById('field2').value+'localized annular / diffuse hair loss with / without scarring in a  pattern with +/- hair pull test.\r\n'"/>

                    </div>
                    <legend><?php echo smarty_function_xlt(array('t' => 'Objective'), $this);?>
</legend>
                    <div class="form-group">
                        <div class="col-sm-10 col-sm-offset-1">
                            <textarea name="objective" class="form-control" id="field2" cols="60" rows="6"><?php echo ((is_array($_tmp=$this->_tpl_vars['data']->get_objective())) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
</textarea>
                        </div>
                    </div>
                </fieldset>
                <fieldset>
                    <div class="form-group">
<input type="button" value="Lesion..." onclick="document.getElementById('field3').value=document.getElementById('field3').value+'Lesion of uncertain clinical significance.\r\n'"/>

<input type="button" value="Lesion with biopsy" onclick="document.getElementById('field3').value=document.getElementById('field3').value+'Lesion of uncertain clinical significance.\r\n', document.getElementById('field4').value=document.getElementById('field4').value+'Procedure: Shave biopsy. Risks, including but not limited to bruising, infection, allergic reactions, scarring, need for additional treatments, were discussed. Consent obtained. Skin prepped with alcohol. Anesthesia with 1% lido with epi. Shave biopsy done, specimen sent to lab. Hemostasis with aluminum chloride. Antibiotic and dressing applied. Wound care instructions given to and discussed with pt. Pt tolerated the procedure well.\r\n'"/>

<input type="button" value="BCC" onclick="document.getElementById('field3').value=document.getElementById('field3').value+'Basal cell carcinoma'"/>

<input type="button" value="SCC" onclick="document.getElementById('field3').value=document.getElementById('field3').value+'Squamous cell carcinoma'"/>

<input type="button" value="MM" onclick="document.getElementById('field3').value=document.getElementById('field3').value+'Malignant melanoma'"/>

<input type="button" value="MIS" onclick="document.getElementById('field3').value=document.getElementById('field3').value+'Melanoma in situ'"/>

<input type="button" value="PDB" onclick="document.getElementById('field3').value=document.getElementById('field3').value+', poorly-defined borders.\r\n'"/>

<input type="button" value="AK" onclick="document.getElementById('field3').value=document.getElementById('field3').value+'Actinic keratosis'"/>

<input type="button" value="AKs" onclick="document.getElementById('field3').value=document.getElementById('field3').value+'Actinic keratoses'"/>

<input type="button" value="SK" onclick="document.getElementById('field3').value=document.getElementById('field3').value+'Benign lesion/seborrheic keratosis'"/>

<input type="button" value="SKs" onclick="document.getElementById('field3').value=document.getElementById('field3').value+'Benign lesions/seborrheic keratoses'"/>

<input type="button" value="Nevi" onclick="document.getElementById('field3').value=document.getElementById('field3').value+'Nevi, clinically benign.\r\n'"/>

<input type="button" value="VV" onclick="document.getElementById('field3').value=document.getElementById('field3').value+'Verruca vulgaris.\r\n'"/>

                    </div>
                    <div class="form-group">
                        <input type="button" value="Photodamage" onclick="document.getElementById('field3').value=document.getElementById('field3').value+'Photodamaged skin.\r\n'"/>

                        <input type="button" value="PH of NMSC" onclick="document.getElementById('field3').value=document.getElementById('field3').value+'Personal history of non-melanoma skin cancer.\r\n'"/>

                        <input type="button" value="PH of MM" onclick="document.getElementById('field3').value=document.getElementById('field3').value+'Personal history of melanoma.\r\n'"/>

                        <input type="button" value="FH of MM" onclick="document.getElementById('field3').value=document.getElementById('field3').value+'Family history of melanoma.\r\n'"/>

                        <input type="button" value="S/P Mohs" onclick="document.getElementById('field3').value=document.getElementById('field3').value+', s/p Mohs.\r\n'"/>

                        <input type="button" value="S/P Exc" onclick="document.getElementById('field3').value=document.getElementById('field3').value+', s/p excision.\r\n'"/>

                        <input type="button" value="S/P C&D" onclick="document.getElementById('field3').value=document.getElementById('field3').value+', s/p curettage and desiccation.\r\n'"/>
                    </div>
                    <legend><?php echo smarty_function_xlt(array('t' => 'Assessment'), $this);?>
</legend>
                    <div class="form-group">
                        <div class="col-sm-10 col-sm-offset-1">
                            <textarea name="assessment" class="form-control" id="field3" cols="60" rows="6"><?php echo ((is_array($_tmp=$this->_tpl_vars['data']->get_assessment())) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
</textarea>
                        </div>
                    </div>
                </fieldset>
                <fieldset>
                    <div class="form-group">

<input type="button" value="General" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'I discussed the diagnosis and differential diagnosis, treatment options, risks and benefits of various treatments, and the treatment plan with the patient at length.\r\n'"/>

<input type="button" value="ABCDE" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'I discussed with the patient at length ABCDE of melanoma/skin cancer, sun protection, regular self-examinations, regular skin checks by a dermatologist.\r\n'"/>

<input type="button" value="Mohs Consult" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'I completed a treatment consult. Reviewed with the patient in detail the results of the biopsy and its ramifications, as well as photograph and progress note from the referring provider. Discussed possible recurrences and metastases. Discussed Mohs surgery versus excision versus curettage and desiccation versus radiation versus other modalities in extensive detail.\r\n' + 
'Based on my medical judgement, Mohs surgery is the most appropriate treatment for this cancer compared to other treatments.\r\n' + 'Indications for Mohs surgery/Removal of the patient`s tumor is complicated by the following clinical features: aggressive histologic subtype, poorly-defined clinical tumor borders, tumor within clinical area `H` critical for tissue conservation, tumor size at least 0.6 cm within clinical area `M` critical for tissue conservation, recurrent cancer, large tumor size, immunosuppression due to organ transplant/leukemia/lymphoma, history of radiation to site.\r\n' + 'Discussed risks of bleeding, infection, nerve damage, and absolute need to have a scar. Discussed possible need for repair and various types of repairs in detail. Anticipate flap or graft repair, discussed ramifications of the reconstruction. Mohs booklet was given to the patient.\r\n' + 'F/U for Mohs. Letter to .'"/>

<input type="button" value="Melanoma" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'I reviewed in detail the results of the biopsy and its ramifications. Discussed recurrences and metastases. Discussed increased risk of melanoma in patient and first-degree relatives. Discussed sun protection, regular self-examinations, and regular skin examinations by a dermatologist.\r\n' + 'Discussed excision in detail and appropriate margins for this lesion. Discussed risks of bleeding, infection, nerve damage, and absolute need to have a scar. Discussed possible need for repair and various types of repairs in detail.\r\n'"/>

<input type="button" value="No Recurrence" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'No evidence of recurrence.\r\n'"/>

<input type="button" value="Reassurance" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'Reassurance given to the patient.\r\n'"/>

                    </div>
                    <div class="form-group">

<input type="button" value="Cryo" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'Procedure: Cryosurgery. Risks, including but not limited to pain, blistering, crusting, discoloration, scarring, and recurrence, were discussed. Consent obtained. Cryosurgery done to lesion using liquid nitrogen x 2 freeze/thaw cycles. Wound care instructions given to and discussed with pt. Pt tolerated the procedure well.\r\n'"/>

<input type="button" value="Shave Bx" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'Procedure: Shave biopsy. Risks, including but not limited to bruising, infection, allergic reactions, scarring, need for additional treatments, were discussed. Consent obtained. Skin prepped with alcohol. Anesthesia with 1% lido with epi. Shave biopsy done, specimen sent to lab. Hemostasis with aluminum chloride. Antibiotic and dressing applied. Wound care instructions given to and discussed with pt. Pt tolerated the procedure well.\r\n'"/>

<input type="button" value="Punch Bx" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'Procedure: Punch biopsy. Risks, including but not limited to bruising, infection, allergic reactions, scarring, need for additional treatments, were discussed. Consent obtained. Skin prepped with alcohol. Anesthesia with 1% lido with epi. 3-mm punch biopsy done, specimen sent to lab. Wound closed with 4-0 nylon. Antibiotic and dressing applied. Wound care instructions given to and discussed with pt. Pt tolerated the procedure well.\r\n'"/>

<input type="button" value="Shave" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'Procedure: Shave removal. Risks, including but not limited to bleeding, infection, scarring, discoloration, recurrence, were discussed. Consent obtained. Skin prepped with alcohol. Anesthesia with 1% lidocaine with epi. Lesion was removed using a #15 blade, specimen sent to lab. Hemostasis with aluminum chloride. Antibiotic and dressing applied. Wound care instructions given to and discussed with pt. Pt tolerated the procedure well.\r\n'"/>

<input type="button" value="Excision/Sx" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'Risks, including but not limited to bleeding, infection, scarring, discoloration, recurrence, were discussed. Consent obtained. Skin prepped in the usual manner. Anesthesia with 1% lidocaine with epi. Lesion was excised using a #15 blade, specimen sent to lab. Hemostasis with aluminum chloride. Wound closed with 6-0 nylon. Antibiotic and dressing applied. Wound care instructions given to and discussed with pt. Pt tolerated the procedure well.\r\n'"/>

<input type="button" value="IL Kenalog" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'Procedure: Intralesional Kenalog injection. Risks, including but not limited to bleeding, infection, discoloration, skin atrophy (dimpling), need for multiple treatments discussed. Consent obtained. Skin prepped with alcohol. Kenalog 40 mg/cc x  cc total was injected into lesion. Patient tolerated the procedure well.\r\n'"/>

<input type="button" value="Milia extraction" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'Procedure: Milium extraction. Risks, including but not limited to bruising, infection, recurrence discussed. Consent obtained. 1 lesion was unroofed with #15 blade and extracted using a comedone extractor. Pt tolerated the procedure well.\r\n'"/>

<input type="button" value="I&D" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'Procedure: Incision and drainage. Risks, including but not limited to bruising, infection, allergic reactions, scarring, recurrence, need for additional treatments discussed. Consent obtained. Skin prepped in the usual manner. Anesthesia achieved with 1% lido with epi. Incision performed with a #11 blade and the contents evacuated with pressure. Hemostasis was achieved with pressure. Antibiotic ointment and a pressure dressing were applied. Wound care instructions given to and discussed with pt. Pt tolerated the procedure well.\r\n'"/>

<input type="button" value="PDT (A)" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'Procedure: Photodynamic therapy by provider. Risks, including but not limited to redness, swelling, itching, pain, peeling, crusting, sensitivity to light, discoloration, scarring, need for additional treatments, were discussed. Consent obtained. Treatment area was cleansed with alcohol. 2 g of 10% 5-ALA (Ameluz gel) was applied to the skin by me (provider) and allowed to incubate for 1 hour. After appropriate eye protection to patient and staff, this was followed by exposure to BLU-U light for 16 minutes and 40 seconds as initiated by me (provider). Patient tolerated the procedure well. Complete sun avoidance x 48 hours and strict sun protection thereafter discussed with patient.\r\n'"/>

<input type="button" value="PDT (L)" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'Procedure: Photodynamic therapy. Risks, including but not limited to redness, swelling, itching, pain, peeling, crusting, sensitivity to light, discoloration, scarring, need for additional treatments, were discussed. Consent obtained. Treatment area was cleansed with alcohol. 20% 5-ALA (Levulan Kerastick) was applied to the skin and allowed to incubate for 1 hour. After appropriate eye protection to patient and staff, this was followed by exposure to BLU-U light for 16 minutes and 40 seconds. Patient tolerated the procedure well. Complete sun avoidance x 48 hours and strict sun protection thereafter discussed with patient.\r\n'"/>

<input type="button" value="C&D" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'Procedure: Curettage and desiccation. Risks, including but not limited to bleeding, infection, scarring, discoloration, recurrence, were discussed. Consent obtained. Skin prepped with alcohol. Anesthesia with 1% lidocaine with epi. Curettage was performed using a 3-mm curette, followed by desiccation using a hyfrecator. Procedure repeated 3 times. Antibiotic and dressing applied. Wound care instructions given to and discussed with pt. Pt tolerated the procedure well.\r\n'"/>

                    </div>
                    <div class="form-group">
                        <input type="button" value="Cosmetic consult" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'I performed a cosmetic consult for '"/>

                        <input type="button" value="Xeomin" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'Botulinum toxin injection today. I discussed that etched-in lines will persist. Risks, including but not limited to bruising, headache, lid ptosis (weakness), allergic reactions, asymmetry, need for touch-up, need for additional treatments, as well as cosmetic nature and fees, were discussed. Consent obtained. A total of 16 U of Xeomin were injected into glabella. Patient tolerated the procedure well. Post-treatment instructions were given to and discussed with patient.\r\n'"/>

                        <input type="button" value="Xeomin (Lips)" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'Botulinum toxin injection today. I discussed that etched-in lines will persist. Risks, including but not limited to bruising, headache, lip droop, inability to use a straw or play wind instruments or enunciate certain sounds, allergic reactions, asymmetry, need for touch-up, need for additional treatments, as well as cosmetic nature and fees, were discussed. Consent obtained. A total of 4 U of Xeomin were injected into the upper lip, 2 U into the lower lip. Patient tolerated the procedure well. Post-treatment instructions were given to and discussed with patient.\r\n'"/>

                        <input type="button" value="HA Filler" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'HA filler injection today. Risks, including but not limited to bruising, swelling, nodularity (bumps), allergic reactions, asymmetry, improvement rather than complete correction, need for touch-up, need for additional treatments, as well as cosmetic nature and fees, were discussed. Consent obtained.  syringe of  filler was injected . Patient tolerated the procedure well. Post-treatment instructions were given to and discussed with patient.\r\n'"/>

                        <input type="button" value="Radiesse" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'Radiesse filler injection today. Risks, including but not limited to bruising, swelling, nodularity (bumps), allergic reactions, asymmetry, improvement rather than complete correction, need for touch-up, need for additional treatments, as well as cosmetic nature and fees, were discussed. Consent obtained.  large (1.5-cc) syringes of Radiesse filler were injected under . Patient tolerated the procedure well. Post-treatment instructions were given to and discussed with patient.\r\n'"/>

                        <input type="button" value="Sculptra" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'Sculptra filler injection today. Risks, including but not limited to bruising, swelling, nodularity (bumps), allergic reactions, asymmetry, improvement rather than complete correction, need for touch-up, need for additional treatments, as well as cosmetic nature and fees, were discussed. Discussed that results will take 7-9 months to manifest. Consent obtained. 1 vial of Sculptra aesthetic filler was previously reconstituted with 7 cc of bacteriostatic water and 2 cc of 1% lido with epi and injected as follows: . Patient tolerated the procedure well. Post-treatment massage done. Post-treatment instructions, including massage, were given to and discussed with patient.\r\n'"/>

                    </div>
                    <div class="form-group">
                        <input type="button" value="Skin tag removal" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'Skin tag removal today. Risks, including but not limited to bleeding, infection, scarring, recurrence, as well as cosmetic nature and fees, were discussed. Consent obtained. Anesthesia with 1% lidocaine with epi. Skin tags were removed with gradle scissors. Hemostasis with aluminum chloride. Antibiotic and dressing applied. Wound care instructions given to and discussed with pt. Pt tolerated the procedure well.\r\n'"/>

                        <input type="button" value="SK removal" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'Seborrheic keratosis removal today. Risks, including but not limited to bleeding, infection, scarring, discoloration, recurrence, as well as cosmetic nature and fees, were discussed. Consent obtained. Anesthesia with 1% lidocaine with epi. 3-mm curette was used to remove SK. Hemostasis with aluminum chloride. Antibiotic and dressing applied. Wound care instructions given to and discussed with pt. Pt tolerated the procedure well.\r\n'"/>

                        <input type="button" value="Sclero" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'Sclerotherapy today. Risks, including but not limited to bruising, redness, pain, discoloration, ulceration, scarring, need for multiple treatments, as well as cosmetic nature and fees, were discussed. Consent obtained. Sclerotherapy performed with 2 cc of 23.4% NaCl solution, neutralization with normal saline. Pt tolerated the procedure well. Patient to wear compression stockings for at least 24 hours.\r\n'"/>

                        <input type="button" value="Dermabrasion" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'Procedure: Dermabrasion. Risks, including but not limited to bleeding, infection, scarring, discoloration, need for additional treatments, were discussed. Consent obtained. Skin prepped with alcohol. Anesthesia with 1% lidocaine with epi. Dermabrasion was performed using a diamond fraise. Hemostasis with aluminum chloride. Antibiotic and dressing applied. Wound care instructions given to and discussed with pt. Sun protection discussed. Pt tolerated the procedure well.\r\n'"/>

                        <input type="button" value="Subcision" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'Subcision today. Risks, including but not limited to bruising, bleeding, lumpiness, incomplete correction, infection, scarring, need for additional treatments, as well as cosmetic nature and fees, were discussed. Consent obtained. Skin prepped with alcohol. Anesthesia with 1% lido with epi. Subcision done using a 16G Admix NoKor needle. Antibiotic and dressing applied. Wound care instructions given to and discussed with pt. Pt tolerated the procedure well.\r\n'"/>

                        <input type="button" value="Chemical Peel" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'20% TCA chemical peel today. Risks, including but not limited to infection, allergic reactions, peeling, discoloration, acne flare-ups, scarring, need for additional treatments, as well as cosmetic nature and fees, were discussed. Consent obtained. Skin prepped with alcohol. 20% TCA was applied evenly over the face until white frost was noted, neutralized with water. Petroleum jelly applied to face. Wound care instructions given to and discussed with pt. Pt tolerated the procedure well. Continue Valtrex 500 mg po bid x 1 more week.\r\n'"/>

                        <input type="button" value="TCA-CROSS" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'Procedure: 95% TCA-CROSS. Risks, including but not limited to infection, allergic reactions, peeling, discoloration, scarring, need for additional treatments, as well as cosmetic nature and fees, were discussed. Consent obtained. Skin prepped with alcohol. 95% TCA was applied to the base of the scar using a sharpened Q-tip until white frost was noted, then neutralized with water. Wound care instructions given to and discussed with pt. Pt tolerated the procedure well.\r\n'"/>

                    </div>
                    <div class="form-group">
                        <input type="button" value="Smoothbeam" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'Smoothbeam non-ablative laser treatment today. Risks, including but not limited to redness, pain, blistering, discoloration, need for multiple treatments, as well as cosmetic nature and fees, were discussed. Consent obtained. After appropriate eye protection to patient and staff, Smoothbeam laser was used at the following settings: 4-mm spot size, fluence of 10 J/cm2, and DCD of 30. Pt tolerated the procedure well. Sun protection discussed.\r\n'"/>

                        <input type="button" value="KTP" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'KTP laser treatment today. Risks, including but not limited to redness, pain, blistering, crusting, discoloration, scarring, need for multiple treatments, as well as cosmetic nature and fees, were discussed. Consent obtained. After appropriate eye protection to patient and staff, Laserscope Aura was used at the following settings: 10-ms pulse duration, 1-mm spot size, 10 J/cm2 of fluence. Pt tolerated the procedure well. Sun protection discussed.\r\n'"/>

                        <input type="button" value="IPL" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'IPL/intense pulsed light treatment today. Risks, including but not limited to redness, pain, blistering, crusting, peeling, temporary darkening of freckles, discoloration, need for multiple treatments, as well as cosmetic nature and fees, were discussed. Consent obtained. After appropriate eye protection to patient and staff, Cynergy PL was used at the following settings: 560-nm filter/handpiece, 20-ms pulse duration, 10 J/cm2 of fluence, contact cooling, and chilled gel. Pt tolerated the procedure well. Sun protection discussed.\r\n'"/>

                        <input type="button" value="QS Laser" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'Q-switched laser treatment today. Risks, including but not limited to redness, bruising, pain, blistering, crusting, peeling, discoloration, scarring, need for multiple treatments, as well as cosmetic nature and fees, were discussed. Consent obtained. After appropriate eye protection to patient and staff, Trilase Q-switched laser was used at the following settings: wavelength of 1064 nm, 1.5-mm spot size, energy of 600 J. Pt tolerated the procedure well. Antibiotic ointment and dressing applied. Wound care and sun protection discussed.\r\n'"/>

                        <input type="button" value="TR" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'Q-switched laser tattoo removal today. Risks, including but not limited to redness, pain, bleeding, blistering, crusting, peeling, discoloration, scarring, need for multiple treatments, residual ghost of the tattoo, as well as cosmetic nature and fees, were discussed. Consent obtained. Anesthesia with topical EMLA cream. After appropriate eye protection to patient and staff, Trilase Q-switched Nd:YAG laser was used at the following settings: 1.5-mm spot size, energy of 500 J. Pt tolerated the procedure well. Antibiotic ointment and dressing applied. Wound care and sun protection discussed.\r\n'"/>

                        <input type="button" value="Vasc/Nd:YAG" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'Nd:YAG laser treatment today. Risks, including but not limited to redness, pain, blistering, crusting, scarring, discoloration, need for multiple treatments, as well as cosmetic nature and fees, were discussed. Consent obtained. After appropriate eye protection to patient and staff, Laserscope Lyra long-pulsed Nd:YAG laser was used at the following settings: 5-mm spot size, pulse duration of 55 ms, fluence of 90 J/cm2, and chilled plate at -10 C. Pt tolerated the procedure well. Sun protection discussed.\r\n'"/>

                        <input type="button" value="HR/Nd:YAG" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'Laser hair removal today. Emphasized temporary hair reduction rather than complete and permanent hair removal. Risks, including but not limited to redness, pain, blistering, crusting, scarring, discoloration, need for multiple treatments, as well as cosmetic nature and fees, were discussed. Consent obtained. After appropriate eye protection to patient and staff, Laserscope Lyra long-pulsed Nd:YAG laser was used at the following settings: 5-mm spot size, pulse duration of 45 ms, fluence of 75 J/cm2, and chilled plate at -10 C. Pt tolerated the procedure well. Sun protection discussed.\r\n'"/>

                        <input type="button" value="VBeam/PDL" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'Pulsed-dye laser treatment today. Risks, including but not limited to purpura, redness, blistering, discoloration, need for multiple treatments, as well as cosmetic nature and fees, were discussed. Consent obtained. After appropriate eye protection to patient and staff, V-beam laser was used at the following settings: pulse duration of 1.5 ms, 10-mm spot size, fluence of 7.0 J/cm2, and DCD of 30/30. Pt tolerated the procedure well. Sun protection discussed.\r\n'"/>

                        <input type="button" value="Fractional CO2" onclick="document.getElementById('field4').value=document.getElementById('field4').value+'Fractional ablative laser treatment today. Risks, including but not limited to redness, swelling, pain, blistering, crusting, peeling, bronzing, discoloration, lines of demarcation, improvement rather than complete correction, need for multiple treatments, as well as cosmetic nature and fees, were discussed. Consent obtained. Anesthesia achieved using EMLA cream. After appropriate eye protection to patient and staff, SmartSkin fractional CO2 laser was used at the following settings: power of 20W, pitch of 550 microns, dwell time of 2000 ms, and autofill scanner setting. Light feathering performed at margins to reduce the risk of demarcation lines. Pt tolerated the procedure well. Petroleum jelly applied to treated areas. Sun protection and skin care discussed.\r\n'"/>

                    </div>
                    <div class="form-group">
                        <input type="button" value="Dermatitis counseling" onclick="document.getElementById('field4').value= document.getElementById('field4').value+'Emphasized the need for consistent use of emollients. Recommend use of gentle cleansers and avoidance of strong soaps that contain dyes, fragrances, or desiccating ingredients. Dry Skin Care handout reviewed and given to the patient today.\r\n'"/>

                        <input type="button" value="Steroid counseling" onclick="document.getElementById('field4').value= document.getElementById('field4').value+'Counseled patient on proper use of steroid medication and the potential for adverse effects, including striae, discoloration of skin, and skin atrophy. Counseled patient to avoid using this medication in the armpits, groin, and face.\r\n'"/>

                        <input type="button" value="Doxy counseling" onclick="document.getElementById('field4').value= document.getElementById('field4').value+'Counseled patient on the use of doxycycline and emphasized the need to take the medication with food to avoid GI symptoms. Counseled patient to avoid laying flat for 2 hours after taking medication due to the risk of esophagitis. Counseled patient to avoid vitamins and dairy products within 1-2 hours of medication. Advised patient to practice good sun avoidance while taking this medication as it can increase photosensitivity. Discussed diligently using  suncreen and/or UPF clothing if sun exposure cannot be avoided.\r\n'"/>

                        <input type="button" value="Rosacea counseling" onclick="document.getElementById('field4').value= document.getElementById('field4').value+'Counseled patient on the nature of this condition. Counseled patient on common triggers, including spicy foods, alcohol, heat, UV exposure, chemical skin products, wind, cold, stress. Emphasized the need for daily sunscreen application and highly recommend mineral based sunscreen with a minimum SPF of 30. Counseled patient about various treatment options including topical cleansers, topical medications, oral medications, and lasers. Discussed the chronic nature of this condition. Discussed gentle skin care regimen.\r\n'"/>

                        <input type="button" value="Retinoid counseling" onclick="document.getElementById('field4').value= document.getElementById('field4').value+'Counseled patient on the use of retinoids. Discussed the proper application and distribution of medication. Advised patient to begin using every other night for a few weeks, then increase to nightly as tolerated. Counseled patient on avoiding application to the eyelids and lips. Recommend patient apply medication to a clean, dry face and follow with an emollient moisturizer. Counseled patient to wash the face in the morning and use suncreen every morning. Counseled patient on possible side effects and adverse effects, such as redness, dryness, scaling skin, sun sensitivity, burning, and peeling.\r\n'"/>

                    </div>
                    <div class="form-group">
                        <input type="button" value="Discharge (replace)" onclick="document.getElementById('field4').value='Healed well. Patient to continue to follow up with , prn with me.\r\n'"/>

                        <input type="button" value="Discharge (add)" onclick="document.getElementById('field4').value= document.getElementById('field4').value+'Healed well. Patient to continue to follow up with , prn with me.\r\n'"/>

                        <input type="button" value="Referral" onclick="document.getElementById('field4').value= document.getElementById('field4').value+'Patient was also referred to Dr. for regular skin checks.\r\n'"/>

                        <input type="button" value="Smoking counseling" onclick="document.getElementById('field4').value= document.getElementById('field4').value+'Smoking and smoking cessation aids discussed with the patient.\r\n'"/>

                    </div>
                    <legend><?php echo smarty_function_xlt(array('t' => 'Plan'), $this);?>
</legend>
                    <div class="form-group">
                        <div class="col-sm-10 col-sm-offset-1">
                            <textarea name="plan" class="form-control" id="field4" cols="60" rows="6"><?php echo ((is_array($_tmp=$this->_tpl_vars['data']->get_plan())) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
</textarea>
                        </div>
                    </div>
                </fieldset>
                <div class="form-group clearfix">
                    <div class="col-sm-10 col-sm-offset-1 position-override">
                        <div class="btn-group oe-opt-btn-group-pinch" role="group">
                            <button type="submit" class="btn btn-default btn-save" name="Submit"><?php echo smarty_function_xlt(array('t' => 'Save'), $this);?>
</button>
                            <button type="button" class="btn btn-link btn-cancel oe-opt-btn-separate-left" onclick="top.restoreSession(); location.href='<?php echo $this->_tpl_vars['DONT_SAVE_LINK']; ?>
';"><?php echo smarty_function_xlt(array('t' => 'Cancel'), $this);?>
</button>
                        </div>
                        <input type="hidden" name="id" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['data']->get_id())) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
" />
                        <input type="hidden" name="activity" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['data']->get_activity())) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
">
                        <input type="hidden" name="pid" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['data']->get_pid())) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
">
                        <input type="hidden" name="process" value="true">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>