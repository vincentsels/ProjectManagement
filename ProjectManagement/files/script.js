function ShowOrHide( p_div ) { 
	t_div = document.getElementById( p_div );
	t_expand_icon = document.getElementById( p_div + "_img" );

	if ( t_div.className.indexOf( "hidden" ) >= 0 ) {
		t_div.className = Trim( t_div.className.replace( "hidden", "" ) );
		t_expand_icon.src = "images/minus.png";
	} else {
		t_div.className = Trim( "hidden " + t_div.className );
		t_expand_icon.src = "images/plus.png";
	}
}