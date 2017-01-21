<import file="header"/>
<input type="button" value="Add panel" data-popup="panels" onClick="add()" class="addButton popup__open">
<script>
var panels = ${json_encode(${data.panels})};
var resources = ${json_encode(${data.resources})};
</script>
   
<div class="user-table">    
	<!-- user-table__header -->
    <div class="user-table__header">
        <div>Id</div>
        <div>Name</div>
        <div>Path</div>
        <div>Permissions</div>
    </div>
    <!-- /user-table__header -->

    <!-- user-table__content -->
    <div class="user-table__content">
        <standard:foreach var="${data.panels}" value="panelInfo1">
            <standard:if condition="${panelInfo1.panel.parentId}==0">
        		<cms:panel id="${panelInfo1.panel.id}" isParent="1"/>
        		<standard:foreach var="${data.panels}" value="panelInfo2">
            		<standard:if condition="${panelInfo2.panel.parentId}==${panelInfo1.panel.id}">
            		<cms:panel id="${panelInfo2.panel.id}" isParent="0"/>
            		</standard:if>
        		</standard:foreach>
        		<standard:foreach var="${data.resources}" value="resourceInfo">
            		<standard:if condition="${panelInfo1.panel.id}==${resourceInfo.resource.panelId}">
            		<cms:resource id="${resourceInfo.resource.id}"/>
            		</standard:if>
        		</standard:foreach>
        	</standard:if>
        </standard:foreach>
    </div>
    <!-- /user-table__content -->
</div>
    
<!-- popup -->
<div class="popup">

    <!-- popup__wrap -->
    <div class="popup__wrap">

        <!-- popup__content -->
        <div class="popup__content popup__panels">
	    	<input type="hidden" id="template:parent_id" value="0"/>

            <h2 class="popup__content-title popup__content-title_2 " id="template:title"></h2>

            <!-- popup-form -->
            <div class="popup-form">

                <!-- popup-form__items -->
                <div class="table">
                	<div>
                		<div>ID:</div>
                		<input type="text" id="template:id" value="0" disabled/>
                	</div>
                	<div>
                		<div>Name:</div>
                		<input type="text" id="template:name"/>
                	</div>
                	<div>
                		<div>Path:</div>
                		<input type="text" id="template:url"/>
                	</div>
                	<div>
                		<div>Is public:</div>
                		<input type="checkbox" id="template:is_public" value="1" onChange="toggle(this, 'template:rights')"/>
                	</div>
                	<div id="template:rights">
                		<div>Permissions:&nbsp;</div>
    					<div id="template:body">
                		<standard:for var="${data.departments}" value="i">
                		<cms:rights className="permissionEntry panelPermission" departments="data.departments" levels="data.levels" showGroups="0"/>
                		</standard:for>
                		</div>
	    				<input type="button" value="+ More Rights" onClick="Permissions.addPermission('panelPermission')" class="cloneButton"/>
                	</div>
                </div>

                <div class="popup-form__btns nonjustified">
                    <button type="submit" class="btn btn_3" onClick="save()"><span>save</span></button>&nbsp;
                    <a href="#" class="btn btn_3 btn_red popup__cancel"><span>cancel</span></a>
                </div>
                <!-- /popup-form__items -->

            </div>
            <!-- /popup-form -->
        </div>
        <div class="popup__content popup__resources">
	    	<input type="hidden" id="resources:parent_id" value="0"/>

            <h2 class="popup__content-title popup__content-title_2 " id="resources:title"></h2>

            <!-- popup-form -->
            <div class="popup-form">

                <!-- popup-form__items -->
                <div class="table">
                	<div>
                		<div>ID:</div>
                		<input type="text" id="resources:id" value="0" disabled/>
                	</div>
                	<div>
                		<div>Name:</div>
                		<input type="text" id="resources:name"/>
                	</div>
                	<div id="template:rights">
                		<div>Permissions:</div>
    					<div id="template:body">
                		<standard:for var="${data.departments}" value="i">
                		<cms:rights className="permissionEntry resourcePermission" departments="data.departments" levels="data.levels" showGroups="0"/>
                		</standard:for>
                		</div>
	    				<input type="button" value="+ More Rights" onClick="Permissions.addPermission('resourcePermission')" class="cloneButton"/>
                	</div>
                </div>

                <div class="popup-form__btns nonjustified">
                    <button type="submit" class="btn btn_3" onClick="saveResource()"><span>save</span></button>&nbsp;
                    <a href="#" class="btn btn_3 btn_red popup__cancel"><span>cancel</span></a>
                </div>
                <!-- /popup-form__items -->

            </div>
            <!-- /popup-form -->
        </div>
        <!-- /popup__content -->

    </div>
    <!-- /popup__wrap -->

</div>
<!-- /popup -->
<import file="footer"/>