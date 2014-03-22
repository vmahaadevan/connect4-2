/******************************************************************************
* Tzuo Shuin Yew (997266499)
* Chia-Heng Lin (997530970)
* April 4, 2014
* @file detectWin.js
* @brief client-side detection of winning scenarios on the connect-four board
* @details When the user makes a move, this file checks if it's a winning move
******************************************************************************/
// 2D array, initially every element is empty
var board = [];
var COLS = 7;
var ROWS = 6;
for (var i=0; i<ROWS; i++){
	board[i] = [];
	for (var j=0;j<COLS;j++){
		board[i][j] = "";
	}
}
// populate the board based on the "filled" array sent by the board jquery
function populate (filled){
	for (var key in filled) {
		var i = key[3];
		var j = key[1];
		board[i][j] = filled[key];
	}
}

// Helper functions to determine a win

function checkColumn(col, user) {
	var count = 0;
	for (var i=0; i<ROWS; i++){
		if (board[i][col]==user)
			count++;
		else if (count>0)
			break;
	}
	return (count>=4);
}

function checkRow(row, user){
	var count = 0;
	for (var j=0; j<COLS; j++){
		if (board[row][j]==user)
			count++;
		else if (count>0)
			break;
	}
	return (count>=4);
}
// starting from northwest neighbour, go southeast
function checkDiagonalNW(row,col,user){
	if (row==0 || col==0)
		return false;
	var count = 0;
	var i=row-1;
	var j=col-1;
	while(i<ROWS && j<COLS){
		if (board[i][j]==user)
			count++;
		else if (count>0)
			break;
		i++;
		j++;
	}
	return (count>=4);
}
// starting from southeast neighbour, go northwest
function checkDiagonalSE(row,col,user){
	if (row==ROWS-1 || col==COLS-1)
		return false;
	var count = 0;
	var i=row+1;
	var j=col+1;
	while(i>=0 && j>=0){
		if (board[i][j]==user)
			count++;
		else if (count>0)
			break;
		i--;
		j--;
	}
	return (count>=4);
}
// starting from southwest neighbour, go northeast
function checkDiagonalSW(row,col,user){
	if (row==ROWS-1 || col==0)
		return false;
	var count = 0;
	var i=row+1, j=col-1;
	while(i>=0 && j<COLS){
		if (board[i][j]==user)
			count++;
		else if (count>0)
			break;
		i--;
		j++;
	}
	return (count>=4);
}
// starting from northeast neighbour, go southwest
function checkDiagonalNE(row,col,user){
	if (row==0 || col==COLS-1)
		return false;
	var count = 0;
	var i=row-1, j=col+1;
	while(i<ROWS && j>=0){
		if (board[i][j]==user)
			count++;
		else if (count>0)
			break;
		i++;
		j--;
	}
	return (count>=4);
}
// check for win
function checkWin(row,col,user,filled) {
	populate(filled);
	return (checkColumn(col,user) || checkRow(row,user) || checkDiagonalNW(row,col,user)
		|| checkDiagonalSE(row,col,user) || checkDiagonalSW(row,col,user) || 
		checkDiagonalNE(row,col,user));
}
