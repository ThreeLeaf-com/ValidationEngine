# Security

This package is a library. It inherits the host application's security posture
and adds very little of its own, so the two concepts below are mostly about what
the host must supply.

- [Route Protection Is the Host Application's Duty](route-protection.md) - No authentication or authorization ships with the package
- [Rule Class Instantiation From Stored Data](rule-class-instantiation.md) - `rule_type` names a class the engine instantiates, and the checks that bound it

Related: [Rule Persistence and Instantiation](/features/rule-persistence-and-instantiation.md)
describes the same machinery from the functional side.
