function validateRules(){
	var _this=this;
	 _this.isNull = function (str) {
        return (str == "" || typeof str != "string");
    };
     _this.isPwd = function (str) {
        return /[a-zA-Z0-9]+/.test(str);
    };
    _this.isNumber = function (str) {
    	return /[0-9]+/.test(str);
    }
}
window["validateRules"] = new validateRules();
function validateSetting(){
	var _this=this;
	_this.regScoreName=function(value){
		if(window.validateRules.isNull(value)){
			message="请输入评分名称";
			return{
				errorNo:1,
				message:message,
			}
		}
		return{errorNo:0}
	};
	_this.regScoreStandard=function(value){
		if(window.validateRules.isNull(value)){
			message="请输入评分项";
			return{
				errorNo:1,
				message:message,
			}
		}
		return{errorNo:0}
	};
	_this.regScoreReason=function(value){
		if(window.validateRules.isNull(value)){
			message="请输入评分依据";
			return{
				errorNo:1,
				message:message,
			}
		}
		return{errorNo:0}
	};
	_this.regScoreHighest=function(value){
		if(window.validateRules.isNull(value)){
			message="请输入最高分值";
			return{
				errorNo:1,
				message:message,
			}
		}
		if(!window.validateRules.isNumber(value)){
			message="请输入数字";
			return{
				errorNo:2,
				message:message,
			}
		}
		if(value > 100){
			message="最高分值不能超过100";
			return{
				errorNo:3,
				message:message,
			}
		}
		return{errorNo:0}
	};
	_this.regExportGroupName=function(value){
		if(window.validateRules.isNull(value)){
			message="请输入专家组名称";
			return{
				errorNo:1,
				message:message,
			}
		}
		return{errorNo:0}
	};
	_this.regExportName=function(value){
		if(window.validateRules.isNull(value)){
			message="请输入专家姓名";
			return{
				errorNo:1,
				message:message,
			}
		}
		return{errorNo:0}
	};
	_this.regLoginName=function(value){
		if(window.validateRules.isNull(value)){
			message="请输入登录账号";
			return{
				errorNo:1,
				message:message,
			}
		}
		if(!window.validateRules.isPwd(value)){
			message="请输入正确的账号，只可输入英文和数字";
			return{
				errorNo:2,
				message:message,
			}
		}
		return{errorNo:0}
	};
	_this.regLoginPwd=function(value){
		if(window.validateRules.isNull(value)){
			message="请输入登录密码";
			return{
				errorNo:1,
				message:message,
			}
		}
		if(!window.validateRules.isPwd(value)){
			message="请输入正确的密码，只可输入英文和数字";
			return{
				errorNo:2,
				message:message,
			}
		}
		return{errorNo:0}
	};
	_this.regLoginCode=function(value){
		if(window.validateRules.isNull(value)){
			message="请输入验证码";
			return{
				errorNo:1,
				message:message,
			}
		}
		return{errorNo:0}
	};
}
window["validateSetting"]= new validateSetting();