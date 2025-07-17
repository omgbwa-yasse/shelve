import React, { useState } from 'react';
import { toast } from 'react-toastify';
import { Link } from 'react-router-dom';
import {
  FaUser,
  FaLock,
  FaSignInAlt,
  FaEye,
  FaEyeSlash
} from 'react-icons/fa';
import {
  AuthContainer,
  AuthForm,
  AuthTitle,
  FormGroup,
  Label,
  Input,
  InputGroup,
  InputIcon,
  PasswordToggle,
  SubmitButton,
  AuthLinks,
  AuthLink,
  ErrorMessage,
  LoadingSpinner
} from '../styles/AuthStyles';

const LoginPage = () => {
  const [formData, setFormData] = useState({
    email: '',
    password: ''
  });
  const [showPassword, setShowPassword] = useState(false);
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState({});

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
    
    // Clear error when user starts typing
    if (errors[name]) {
      setErrors(prev => ({
        ...prev,
        [name]: ''
      }));
    }
  };

  const validateForm = () => {
    const newErrors = {};

    if (!formData.email.trim()) {
      newErrors.email = 'L\'email est requis';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
      newErrors.email = 'Format d\'email invalide';
    }

    if (!formData.password.trim()) {
      newErrors.password = 'Le mot de passe est requis';
    } else if (formData.password.length < 6) {
      newErrors.password = 'Le mot de passe doit contenir au moins 6 caractères';
    }

    return newErrors;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    const newErrors = validateForm();
    if (Object.keys(newErrors).length > 0) {
      setErrors(newErrors);
      return;
    }

    setLoading(true);
    setErrors({});

    try {
      // Simulation d'une requête API
      await new Promise(resolve => setTimeout(resolve, 1500));
      
      // Simulation d'une connexion réussie
      toast.success('Connexion réussie ! Bienvenue dans votre espace personnel.');
      
      // Redirection vers la page d'accueil ou dashboard
      // window.location.href = '/';
      
    } catch (error) {
      console.error('Login error:', error);
      toast.error('Erreur de connexion. Vérifiez vos identifiants.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <AuthContainer>
      <AuthForm onSubmit={handleSubmit}>
        <AuthTitle>
          <FaSignInAlt />
          Connexion
        </AuthTitle>

        <FormGroup>
          <Label htmlFor="email">Adresse email</Label>
          <InputGroup>
            <InputIcon>
              <FaUser />
            </InputIcon>
            <Input
              type="email"
              id="email"
              name="email"
              value={formData.email}
              onChange={handleChange}
              placeholder="votre@email.com"
              hasError={!!errors.email}
            />
          </InputGroup>
          {errors.email && <ErrorMessage>{errors.email}</ErrorMessage>}
        </FormGroup>

        <FormGroup>
          <Label htmlFor="password">Mot de passe</Label>
          <InputGroup>
            <InputIcon>
              <FaLock />
            </InputIcon>
            <Input
              type={showPassword ? 'text' : 'password'}
              id="password"
              name="password"
              value={formData.password}
              onChange={handleChange}
              placeholder="Votre mot de passe"
              hasError={!!errors.password}
            />
            <PasswordToggle 
              type="button"
              onClick={() => setShowPassword(!showPassword)}
            >
              {showPassword ? <FaEyeSlash /> : <FaEye />}
            </PasswordToggle>
          </InputGroup>
          {errors.password && <ErrorMessage>{errors.password}</ErrorMessage>}
        </FormGroup>

        <SubmitButton type="submit" disabled={loading}>
          {loading ? <LoadingSpinner /> : <><FaSignInAlt /> Se connecter</>}
        </SubmitButton>

        <AuthLinks>
          <AuthLink>
            <Link to="/forgot-password">Mot de passe oublié ?</Link>
          </AuthLink>
          <AuthLink>
            Pas encore de compte ? <Link to="/register">S'inscrire</Link>
          </AuthLink>
        </AuthLinks>
      </AuthForm>
    </AuthContainer>
  );
};

export default LoginPage;
