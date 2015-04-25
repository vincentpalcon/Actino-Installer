/*
@(#)File:           $RCSfile: install.js $
@(#)Last changed:   $Date: 2015/04/02 13:00:00 $
@(#)Purpose:        Setup Installation
@(#)Author:         Vincent Palcon
@(#)Copyright:      (C) Actino Inc. 2014-2015
@(#)Product:        Actino Software
* THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
* "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
* LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
* A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
* HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
* SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
* LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
* DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
* THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
* (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
* OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

var step = 1;
$(document).ready(function(){
    $('a[href=#step1]').on('click', function(){
        $('#begin_install').hide();
        $('#step1').show();
        $('.step').html('1/4');
    });
    $('a[href=#connect_db]').on('click', function(){
        $('.load_connect').show();
        $('#form_connect').submit();
	});
    $('a[href=#datatables]').on('click', function(){
        $('.load').show();
        $('#InstallTables').submit();
    });
    $('a[href=#next]').on('click', function(){
        if(step === 1){
            if($(".tos").prop('checked') == false)
            {
               $(".toserror").show();
               event.stopPropagation();
            } else {
                 $(".toserror").hide();
            }
        }

        if(step < 4)step++;
        if(step === 2){
            $('#step1').hide();
            $('#step2').show();
            $('.step').html('2/4');
        }else if(step === 3){
            $('#step2').hide();
            $('#step3').show();
            $('.step').html('3/4');
        }else if(step === 4){
            $('#step3').hide();
            $('#step4').show();
            $('.step').html('4/4');
        }

    });
    $('a[href=#back]').on('click', function(){
        if(step > 1)step--;
        if(step === 1){
            $('#step2').hide();
            $('#step1').show();
            $('.step').html('1/4');
        }else if(step === 2){
            $('#step3').hide();
            $('#step2').show();
            $('.step').html('2/4');
        }else if(step === 3){
            $('#step4').hide();
            $('#step3').show();
            $('.step').html('3/4');
        }
    });
    $('a[href=#language]').on('click', function(){
        $('#input_language').val($(this).attr('id'));
        $('#ch_language').submit();
    });
});