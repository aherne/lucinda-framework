<import file="header"/>
<table width="100%">
    <thead>
        <tr>
            <th>ID</th>
            <th>EXCEPTION</th>
            <th>FILE</th>
            <th>LINE</th>
            <th>MESSAGE & TRACE</th>
            <th>COUNTER</th>
            <th>LAST DATE</th>
            <th>ACTIONS</th>
        </tr>
    </thead>
    <tbody>
    	<standard:foreach var="${data.bugs.data}" value="info">
        <tr>
            <td>${info.id}</td>
            <td>${info.type}</td>
            <td>${info.file}</td>
            <td>${info.line}</td>
            <td onClick="showTrace('${info.id}')">
            	${info.message}<br/>
            	<div id="trace_${info.id}" class="bugTrace">
            	${htmlspecialchars(${info.trace})}
            	</div>
            </td>
            <td>${info.counter}</td>
            <td nowrap>${info.date}</td>
            <td nowrap>
            	<input type="button" value="MORE INFO" onClick="moreInfo(${info.id})"/>
            	<input type="button" value="DELETE" onClick="remove(${info.id})"/>
            </td>
        </tr>
        </standard:foreach>
    </tbody>
    <tfoot>
    </tfoot>
</table>
<standard:if condition="${data.page}!=0">
<input type="button" value="PREVIOUS" onClick="previous(${data.page})"/>
</standard:if>
<standard:if condition="${data.bugs.total} > ((${data.page}+1)*${data.limit})">
<input type="button" value="NEXT" onClick="next(${data.page})"/>
</standard:if>
<import file="footer"/>
