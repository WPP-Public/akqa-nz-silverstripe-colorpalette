<ul $AttributesHTML>
	<% loop $Options %>
		<li class="$Class<% if $isChecked %> selected<% end_if %>">
			<% if $Up.AllowPicker %>
				<button type="button"
					class="colorpalette__swatch<% if $isChecked %> is-selected<% end_if %>"
					data-color="$Title"
					title="$Value"
					aria-label="$Value"
					<% if $isDisabled %>disabled="disabled"<% end_if %>
					style="background: $Title"></button>
			<% else %>
				<input id="$ID" class="radio" name="$Name" type="radio" value="$Value"<% if $isChecked %> checked<% end_if %><% if $isDisabled %> disabled<% end_if %> />
				<label for="$ID" style="background: $Title"></label>
			<% end_if %>
		</li>
	<% end_loop %>
</ul>
<% if $AllowPicker %>
	<div class="colorpalette__picker">
		<input
			type="text"
			class="text colorpalette__picker-input js-color-picker"
			id="{$ID}_picker"
			name="$Name"
			value="$PickerValue"
			maxlength="7"
			data-palette="$PickerColorsJSON.ATT"
			style="background-color: $PickerValue; color: $PickerTextColor;"
			<% if $Disabled %>disabled="disabled"<% end_if %>
			<% if $Readonly %>readonly="readonly"<% end_if %>
		/>
	</div>
<% end_if %>
