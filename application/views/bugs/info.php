<import file="header"/>

<table>
	<tbody>
		<tr>
			<td colspan=2 class="subTitle"><strong>GENERAL INFORMATION</strong></td>
		</tr>
		<tr>
			<td>ID</td>
			<td>${data.info.id}</td>
		</tr>
		<tr>
			<td>Count</td>
			<td>${data.info.count}</td>
		</tr>
		<tr>
			<td>Date</td>
			<td>${data.info.date}</td>
		</tr>
		<tr>
			<td colspan=2 class="subTitle"><strong>ERROR INFORMATION</strong></td>
		</tr>
		<tr>
			<td>Type</td>
			<td>${data.info.exception.type}</td>
		</tr>
		<tr>
			<td>File</td>
			<td>${data.info.exception.file}</td>
		</tr>
		<tr>
			<td>Line</td>
			<td>${data.info.exception.line}</td>
		</tr>
		<tr>
			<td>Message</td>
			<td>${data.info.exception.message}</td>
		</tr>
		<tr>
			<td>Trace</td>
			<td>${nl2br(${data.info.exception.trace})}</td>
		</tr>
		<tr>
			<td colspan=2 class="subTitle"><strong>ENVIRONMENT INFORMATION</strong></td>
		</tr>
		<tr>
			<td>$_SERVER</td>
			<td>
			<table>
			<standard:foreach var="${data.info.environment.server}" key="key" value="value">
				<cms:bug key="${key}" value="${value}"/>
			</standard:foreach>
			</table>
			</td>
		</tr>
		<tr>
			<td>$_GET</td>
			<td>
			<table>
			<standard:foreach var="${data.info.environment.get}" key="key" value="value">
				<cms:bug key="${key}" value="${value}"/>
			</standard:foreach>
			</table>
			</td>
		</tr>
		<tr>
			<td>$_POST</td>
			<td>
			<table>
			<standard:foreach var="${data.info.environment.post}" key="key" value="value">
				<cms:bug key="${key}" value="${value}"/>
			</standard:foreach>
			</table>
			</td>
		</tr>
		<tr>
			<td>$_FILES</td>
			<td>
			<table>
			<standard:foreach var="${data.info.environment.files}" key="key" value="value">
				<cms:bug key="${key}" value="${value}"/>
			</standard:foreach>
			</table>
			</td>
		</tr>
	</tbody>
</table>

<import file="footer"/>