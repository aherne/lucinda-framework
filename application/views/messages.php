<import file="header"/>
<input type="button" value="Add message" data-popup="update" onClick="add()" class="addButton popup__open">
<script>var messages = ${json_encode(${data.messages})};</script>
<div class="user-table">
    <!-- user-table__header -->
    <div class="user-table__header">
        <div>ID</div>
        <div>CODE</div>
        <div>MESSAGE</div>
        <div>IS ERROR</div>
        <div></div>
    </div>
    <!-- /user-table__header -->

    <!-- user-table__content -->
    <div class="user-table__content">
    	<standard:foreach var="${data.messages}" value="info">
        	<div class="user-table__row">
                <div>${info.id}</div>
                <div>${info.code}</div>
                <div>${info.message}</div>
                <div>
                <standard:if condition="${info.isError}">
                Yes
                <standard:else/>
                No
                </standard:if>
                </div>
                <div>
    				<a class="btn btn_2 btn_edit popup__open" data-popup="update" onClick="javascript:edit(${info.id})"><span>edit</span></a>
    				<a class="btn btn_2 btn_cancel" href="javascript:remove(${info.id})"><span>delete</span></a>
    			</div>
            </div>
        </standard:foreach>
    </div>
    <!-- /user-table__content -->

</div>
    

<!-- popup -->
<div class="popup">

    <!-- popup__wrap -->
    <div class="popup__wrap messages">

        <!-- popup__content -->
        <div class="popup__content popup__update">

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
                		<div>Code:</div>
                		<input type="text" id="template:code"/>
                	</div>
                	<div>
                		<div>Body:</div>
                		<input type="text" id="template:message"/>
                	</div>
                	<div>
                		<div>Is error:&nbsp;</div>
                		<input type="checkbox" id="template:isError" value="1"/>
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
        <!-- /popup__content -->

    </div>
    <!-- /popup__wrap -->

</div>
<!-- /popup -->
<import file="footer"/>
